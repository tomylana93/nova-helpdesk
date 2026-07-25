#!/usr/bin/env bash
set -euo pipefail

# ===========================================================================
# Nova Helpdesk — guarded local production deploy
#
# Usage: ./deploy.sh vX.Y.Z
#
# Deploys an exact *stable* release tag to the production VPS. A reproducible
# release bundle (source + vendor + built assets) is created locally from a
# temporary worktree at the tagged commit, checksummed, transferred, and
# activated on the server via the `current` symlink. The VPS never runs
# Composer or pnpm.
#
# Production secrets (DB credentials, APP_KEY, superadmin, etc.) MUST already
# be provisioned in ${DEPLOY_ROOT}/shared/.env on the server. This script
# never creates, edits, or prints the values of that file.
# ===========================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

fail() {
  echo "ERROR: $*" >&2
  if [ "${DEPLOY_TEST_MODE:-0}" = "1" ]; then
    return 1
  fi
  exit 1
}

# --- Remote safety: application-scoped deployment lock ----------------------
acquire_deploy_lock() {
  local deploy_root=$1
  mkdir "${deploy_root}/.deploy-lock" 2>/dev/null \
    || fail "another deployment is active for ${deploy_root}"
}

release_deploy_lock() {
  local deploy_root=$1
  rmdir "${deploy_root}/.deploy-lock" 2>/dev/null || true
}

# --- Remote safety: post-activation health check -----------------------------
health_check() {
  local url=$1
  local attempts=$2
  local delay=$3
  local attempt
  for ((attempt = 1; attempt <= attempts; attempt++)); do
    if curl --fail --silent --show-error --max-time 10 "$url" >/dev/null; then
      return 0
    fi
    sleep "$delay"
  done
  return 1
}

restart_workers() {
  # Restart realtime workers so supervisor-spawned processes load the new
  # release. Supervisor resolves cwd via the 'current' symlink; long-lived
  # processes (esp. reverb, which has no --max-time) otherwise stay pinned to
  # the old release path until cleanup deletes it, breaking broadcasting.
  # deployer has scoped, password-less sudo for exactly these commands.
  sudo supervisorctl restart nova-helpdesk-reverb nova-helpdesk-queue:
}

# --- Remote safety: atomic symlink switch with health check + rollback -------
activate_release() {
  local deploy_root=$1
  local release_dir=$2
  local previous_release
  previous_release=$(readlink "${deploy_root}/current" 2>/dev/null || true)

  ln -sfn "$release_dir" "${deploy_root}/current.next"
  mv -Tf "${deploy_root}/current.next" "${deploy_root}/current"

  local activate_ok=0
  if restart_workers; then
    if health_check "${HEALTHCHECK_URL:-}" "${HEALTHCHECK_ATTEMPTS:-6}" "${HEALTHCHECK_DELAY:-5}"; then
      activate_ok=1
    fi
  fi

  if [ "$activate_ok" -eq 0 ]; then
    if [ -n "$previous_release" ]; then
      ln -sfn "$previous_release" "${deploy_root}/current.rollback"
      mv -Tf "${deploy_root}/current.rollback" "${deploy_root}/current"
      restart_workers || true
      fail "health check or worker restart failed; restored previous release"
    else
      rm -f "${deploy_root}/current"
      fail "health check or worker restart failed; no previous release to restore"
    fi
  fi
}

# --- Guard: stable tag format only (reject RC / prerelease) ----------------
validate_stable_tag() {
  local tag=$1
  [[ "$tag" =~ ^v[0-9]+\.[0-9]+\.[0-9]+$ ]] \
    || fail "Tag '$tag' is not a stable release tag (expected vMAJOR.MINOR.PATCH, no -rc suffix)"
}

# --- Bundle: archive a staged release directory with a checksum -------------
create_archive() {
  local source_dir=$1
  local release_name=$2
  local output_dir=$3
  local archive="${output_dir}/${release_name}.tar.gz"

  tar -C "$source_dir" -czf "$archive" \
    --exclude='./.git' \
    --exclude='./.github' \
    --exclude='./.env' \
    --exclude='./.env.deploy' \
    --exclude='./node_modules' \
    --exclude='./tests' \
    --exclude='./storage' \
    --exclude='./.serena' \
    --exclude='./.agents' \
    --exclude='./.ai' \
    --exclude='./.claude' \
    --exclude='./.codex' \
    --exclude='./.superpowers' \
    --exclude='./.deploy' \
    --exclude='./deploy.sh' \
    .

  (cd "$output_dir" && sha256sum "${release_name}.tar.gz" > "${release_name}.sha256")
}

cleanup_local_build() {
  if [ -n "${BUILD_WORKTREE:-}" ] && [ -d "$BUILD_WORKTREE" ]; then
    git worktree remove --force "$BUILD_WORKTREE" >/dev/null 2>&1 || true
  fi
  if [ -n "${BUILD_ROOT:-}" ] && [ -d "$BUILD_ROOT" ]; then
    rm -rf "$BUILD_ROOT"
  fi
  if [ -n "${BUNDLE_DIR:-}" ] && [ -d "$BUNDLE_DIR" ]; then
    rm -rf "$BUNDLE_DIR"
  fi
}

# --- Bundle: build production dependencies + assets at the exact tag --------
create_release_bundle() {
  local tag=$1
  local tag_commit=$2
  local output_dir=$3
  local short_hash
  short_hash=$(git rev-parse --short "$tag_commit")
  RELEASE_NAME="${tag}-${short_hash}"
  BUILD_ROOT=$(mktemp -d "${TMPDIR:-/tmp}/nova-release.XXXXXX")
  BUILD_WORKTREE="${BUILD_ROOT}/source"
  trap cleanup_local_build EXIT INT TERM

  git worktree add --detach "$BUILD_WORKTREE" "$tag_commit"
  (
    cd "$BUILD_WORKTREE"
    composer install --no-dev --classmap-authoritative --no-interaction --no-progress
    pnpm install --frozen-lockfile --config.strict-dep-builds=false
    VITE_REVERB_APP_KEY="$REVERB_APP_KEY" \
    VITE_REVERB_HOST="$REVERB_HOST" \
    VITE_REVERB_PORT="$REVERB_PORT" \
    VITE_REVERB_SCHEME="$REVERB_SCHEME" \
      pnpm run build
  )
  mkdir -p "$output_dir"
  create_archive "$BUILD_WORKTREE" "$RELEASE_NAME" "$output_dir"
}

main() {
  DRY_RUN=${DRY_RUN:-0}
  TAG=""
  for arg in "$@"; do
    if [ "$arg" = "--dry-run" ]; then
      DRY_RUN=1
    else
      if [ -n "$TAG" ]; then
        fail "Usage: ./deploy.sh vX.Y.Z [--dry-run]"
      fi
      TAG="$arg"
    fi
  done

  [ -n "$TAG" ] || fail "Usage: ./deploy.sh vX.Y.Z [--dry-run]"
  validate_stable_tag "$TAG"

  # --- Configuration ---------------------------------------------------------
  # Infra targets are environment-specific and NOT tracked. Load them from an
  # ignored .env.deploy (copy .env.deploy.example) or pass them via the
  # environment. No production host/domain defaults are baked into this script.
  if [ -f "${SCRIPT_DIR}/.env.deploy" ]; then
    set -a
    # shellcheck disable=SC1091
    source "${SCRIPT_DIR}/.env.deploy"
    set +a
  fi

  if [ "$DRY_RUN" = "1" ]; then
    DEPLOY_HOST=${DEPLOY_HOST:-"dry-run-host"}
    DEPLOY_ROOT=${DEPLOY_ROOT:-"/dry-run-root"}
    REVERB_HOST=${REVERB_HOST:-"dry-run-reverb"}
    HEALTHCHECK_URL=${HEALTHCHECK_URL:-"http://dry-run-healthcheck"}
  fi

  : "${DEPLOY_HOST:?Set DEPLOY_HOST (e.g. deployer@host) in .env.deploy or the environment}"
  : "${DEPLOY_ROOT:?Set DEPLOY_ROOT (e.g. /srv/nova-helpdesk) in .env.deploy or the environment}"
  : "${REVERB_HOST:?Set REVERB_HOST (e.g. helpdesk.example.com) in .env.deploy or the environment}"
  # The client bundle is built locally from a clean worktree with no .env, so the
  # public Reverb app key must be supplied here or Vite bakes key:undefined into
  # the bundle and useEchoNotification crashes at setup in production.
  : "${REVERB_APP_KEY:?Set REVERB_APP_KEY (public Reverb app key, matching the server shared .env) in .env.deploy or the environment}"
  : "${REVERB_PORT:=443}"
  : "${REVERB_SCHEME:=https}"
  : "${KEEP_RELEASES:=3}"
  : "${HEALTHCHECK_URL:?Set HEALTHCHECK_URL (public endpoint returning 2xx when ready) in .env.deploy or the environment}"
  : "${HEALTHCHECK_ATTEMPTS:=6}"
  : "${HEALTHCHECK_DELAY:=5}"

  SHARED_ENV="${DEPLOY_ROOT}/shared/.env"

  # --- Guard: required local binaries ----------------------------------------
  for bin in git composer pnpm ssh rsync tar sha256sum; do
    if ! command -v "$bin" >/dev/null 2>&1; then
      fail "Required command not found: $bin"
    fi
  done

  # --- Guard: tag exists locally ---------------------------------------------
  if ! git rev-parse -q --verify "refs/tags/${TAG}" >/dev/null 2>&1; then
    fail "Tag '$TAG' does not exist locally. Run: git fetch origin --tags"
  fi

  # --- Guard: clean working tree ---------------------------------------------
  if [ -n "$(git status --porcelain)" ]; then
    fail "Working tree is dirty. Commit or stash changes before deploying."
  fi

  # --- Guard: HEAD is exactly the tagged commit ------------------------------
  TAG_COMMIT="$(git rev-list -n 1 "$TAG")"
  HEAD_COMMIT="$(git rev-parse HEAD)"
  if [ "$TAG_COMMIT" != "$HEAD_COMMIT" ]; then
    fail "HEAD ($HEAD_COMMIT) is not the '$TAG' commit ($TAG_COMMIT). Run: git checkout $TAG"
  fi

  # --- Guard: tag is published on origin and part of released main -----------
  # Enforces Release Please as the source of truth: reject local-only or forged
  # tags that never went through the dev -> main -> Release Please flow.
  if ! git fetch --quiet origin main --tags; then
    fail "Unable to fetch origin (network required to verify the release tag)."
  fi
  # Resolve origin's tag commit (peeled for annotated tags; plain ref for lightweight).
  REMOTE_TAG_COMMIT="$(git ls-remote origin "refs/tags/${TAG}^{}" | awk '{print $1}')"
  if [ -z "$REMOTE_TAG_COMMIT" ]; then
    REMOTE_TAG_COMMIT="$(git ls-remote origin "refs/tags/${TAG}" | awk '{print $1}')"
  fi
  if [ -z "$REMOTE_TAG_COMMIT" ]; then
    fail "Tag '$TAG' is not published on origin. Only Release Please tags may be deployed."
  fi
  if [ "$REMOTE_TAG_COMMIT" != "$TAG_COMMIT" ]; then
    fail "Local tag '$TAG' ($TAG_COMMIT) does not match origin's tag commit ($REMOTE_TAG_COMMIT). A moved local tag is not deployable; re-fetch tags."
  fi
  if ! git merge-base --is-ancestor "$TAG_COMMIT" origin/main; then
    fail "Tag '$TAG' ($TAG_COMMIT) is not reachable from origin/main. Deploy only stable releases merged to main."
  fi

  # --- Guard: config/version.php matches the tag (without leading v) ----------
  EXPECTED_VERSION="${TAG#v}"
  if ! grep -q "'app' => '${EXPECTED_VERSION}'" config/version.php; then
    fail "config/version.php does not contain version '${EXPECTED_VERSION}'"
  fi

  SHORT_HASH="$(git rev-parse --short HEAD)"
  RELEASE_NAME="${TAG}-${SHORT_HASH}"
  RELEASE_DIR="${DEPLOY_ROOT}/releases/${RELEASE_NAME}"

  echo "=== Deploying Nova Helpdesk ${TAG} (${SHORT_HASH}) to ${DEPLOY_HOST} ==="

  # --- Guard: production shared .env must already exist -----------------------
  if [ "$DRY_RUN" = "0" ]; then
    echo "Verifying provisioned production env..."
    if ! ssh "$DEPLOY_HOST" "[ -f '${SHARED_ENV}' ]"; then
      fail "Missing ${SHARED_ENV} on server. Provision it before deploying (see CONTRIBUTING.md)."
    fi

    # --- Remote preflight: required env vars present (names only, no values) ----
    echo "Checking required variables in shared .env (names only)..."
    ssh "$DEPLOY_HOST" bash -s -- "$SHARED_ENV" <<'PREFLIGHT'
set -euo pipefail
SHARED_ENV="$1"
# APP_KEY is intentionally omitted: it may be generated on first deploy (see below).
required=(APP_ENV DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD)
missing=()
for var in "${required[@]}"; do
  grep -Eq "^${var}=.+" "$SHARED_ENV" || missing+=("$var")
done
if [ "${#missing[@]}" -gt 0 ]; then
  echo "ERROR: Missing or empty required variables in shared .env: ${missing[*]}" >&2
  exit 1
fi
PREFLIGHT
  fi

  # --- Step 1: Build the release bundle from the exact tagged commit ---------
  echo "Building release bundle in a temporary worktree..."
  BUNDLE_DIR=$(mktemp -d "${TMPDIR:-/tmp}/nova-bundle.XXXXXX")
  create_release_bundle "$TAG" "$TAG_COMMIT" "$BUNDLE_DIR"

  if [ "$DRY_RUN" = "1" ]; then
    echo "Dry-run: local release bundle created successfully at ${BUNDLE_DIR}."
    echo "Remote deployment skipped."
    return 0
  fi

  # --- Step 2: Transfer the bundle -------------------------------------------
  echo "Uploading release bundle to VPS..."
  ssh "$DEPLOY_HOST" "mkdir -p '${DEPLOY_ROOT}/incoming'"
  rsync -avz \
    "${BUNDLE_DIR}/${RELEASE_NAME}.tar.gz" \
    "${BUNDLE_DIR}/${RELEASE_NAME}.sha256" \
    "${DEPLOY_HOST}:${DEPLOY_ROOT}/incoming/"

  # --- Step 3: Remote release tasks ------------------------------------------
  # Function definitions are injected with `declare -f` so the remote script
  # shares the exact lock/health/rollback implementations that the local test
  # suite exercises.
  echo "Running remote tasks..."
  {
    declare -f fail acquire_deploy_lock release_deploy_lock health_check restart_workers activate_release
    cat <<'REMOTE'
set -euo pipefail
DEPLOY_ROOT="$1"
RELEASE_DIR="$2"
RELEASE_NAME="$3"
KEEP_RELEASES="$4"
HEALTHCHECK_URL="$5"
HEALTHCHECK_ATTEMPTS="$6"
HEALTHCHECK_DELAY="$7"
SHARED="${DEPLOY_ROOT}/shared"

# Required remote binaries (the VPS never runs Composer or pnpm)
for bin in php curl tar sha256sum; do
  command -v "$bin" >/dev/null 2>&1 || fail "Required remote command not found: $bin"
done

# One deployment at a time per application root.
acquire_deploy_lock "$DEPLOY_ROOT"
trap 'release_deploy_lock "$DEPLOY_ROOT"' EXIT INT TERM

# Verify and extract the bundle into a fresh release directory. The bundle
# already contains vendor and built assets; the VPS installs nothing.
echo "Verifying bundle checksum and extracting..."
mkdir -p "$RELEASE_DIR"
cd "${DEPLOY_ROOT}/incoming"
sha256sum --check "${RELEASE_NAME}.sha256"
tar -xzf "${RELEASE_NAME}.tar.gz" -C "$RELEASE_DIR"
rm -f "${RELEASE_NAME}.tar.gz" "${RELEASE_NAME}.sha256"

# Shared storage directories (idempotent)
echo "Preparing shared storage..."
mkdir -p "${SHARED}/storage/app/public" \
         "${SHARED}/storage/framework/cache/data" \
         "${SHARED}/storage/framework/sessions" \
         "${SHARED}/storage/framework/views" \
         "${SHARED}/storage/logs"

cd "$RELEASE_DIR"

# Symlink pre-provisioned shared .env + storage (never created here)
echo "Symlinking shared files..."
ln -sfn "${SHARED}/.env" .env
ln -sfn "${SHARED}/storage" storage

# Only generate a key if the provisioned env somehow lacks one
if ! grep -q "APP_KEY=base64:" "${SHARED}/.env"; then
  echo "APP_KEY missing in shared .env; generating one..."
  php artisan key:generate --force
fi

echo "Running database migrations..."
php artisan migrate --force

echo "Syncing roles and permissions..."
php artisan permission:sync-roles --no-interaction

echo "Initializing superadmin user..."
php artisan init:superadmin --no-interaction

echo "Caching Laravel resources..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "Creating storage link..."
php artisan storage:link

echo "Activating release (atomic symlink switch + health check)..."
activate_release "$DEPLOY_ROOT" "$RELEASE_DIR"

echo "Cleaning up old releases (keeping ${KEEP_RELEASES})..."
ls -dt "${DEPLOY_ROOT}/releases/"* | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf
REMOTE
  } | ssh "$DEPLOY_HOST" bash -s -- \
    "$DEPLOY_ROOT" "$RELEASE_DIR" "$RELEASE_NAME" "$KEEP_RELEASES" \
    "$HEALTHCHECK_URL" "$HEALTHCHECK_ATTEMPTS" "$HEALTHCHECK_DELAY"

  echo "=== Deployment ${TAG} (${SHORT_HASH}) finished successfully! ==="
}

if [[ "${BASH_SOURCE[0]}" == "$0" ]]; then
  main "$@"
fi
