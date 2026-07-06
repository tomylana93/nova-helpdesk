#!/usr/bin/env bash
set -euo pipefail

# ===========================================================================
# Nova Helpdesk — guarded local production deploy
#
# Usage: ./deploy.sh vX.Y.Z
#
# Deploys an exact *stable* release tag to the production VPS. Assets are
# built locally, then source + built assets are rsync'd to a tag+commit
# release directory and activated via the `current` symlink.
#
# Production secrets (DB credentials, APP_KEY, superadmin, etc.) MUST already
# be provisioned in ${DEPLOY_ROOT}/shared/.env on the server. This script
# never creates, edits, or prints the values of that file.
# ===========================================================================

# --- Configuration ---------------------------------------------------------
# Infra targets are environment-specific and NOT tracked. Load them from an
# ignored .env.deploy (copy .env.deploy.example) or pass them via the
# environment. No production host/domain defaults are baked into this script.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ -f "${SCRIPT_DIR}/.env.deploy" ]; then
  set -a
  # shellcheck disable=SC1091
  source "${SCRIPT_DIR}/.env.deploy"
  set +a
fi

: "${DEPLOY_HOST:?Set DEPLOY_HOST (e.g. deployer@host) in .env.deploy or the environment}"
: "${DEPLOY_ROOT:?Set DEPLOY_ROOT (e.g. /srv/nova-helpdesk) in .env.deploy or the environment}"
: "${REVERB_HOST:?Set REVERB_HOST (e.g. helpdesk.example.com) in .env.deploy or the environment}"
: "${REVERB_PORT:=443}"
: "${REVERB_SCHEME:=https}"
: "${KEEP_RELEASES:=3}"

SHARED_ENV="${DEPLOY_ROOT}/shared/.env"

fail() {
  echo "ERROR: $*" >&2
  exit 1
}

# --- Guard: exactly one argument -------------------------------------------
[ "$#" -eq 1 ] || fail "Usage: ./deploy.sh vX.Y.Z"
TAG="$1"

# --- Guard: required local binaries ----------------------------------------
for bin in git pnpm ssh rsync; do
  command -v "$bin" >/dev/null 2>&1 || fail "Required command not found: $bin"
done

# --- Guard: stable tag format only (reject RC / prerelease) ----------------
if ! [[ "$TAG" =~ ^v[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  fail "Tag '$TAG' is not a stable release tag (expected vMAJOR.MINOR.PATCH, no -rc suffix)"
fi

# --- Guard: tag exists locally ---------------------------------------------
git rev-parse -q --verify "refs/tags/${TAG}" >/dev/null 2>&1 \
  || fail "Tag '$TAG' does not exist locally. Run: git fetch origin --tags"

# --- Guard: clean working tree ---------------------------------------------
[ -z "$(git status --porcelain)" ] \
  || fail "Working tree is dirty. Commit or stash changes before deploying."

# --- Guard: HEAD is exactly the tagged commit ------------------------------
TAG_COMMIT="$(git rev-list -n 1 "$TAG")"
HEAD_COMMIT="$(git rev-parse HEAD)"
[ "$TAG_COMMIT" = "$HEAD_COMMIT" ] \
  || fail "HEAD ($HEAD_COMMIT) is not the '$TAG' commit ($TAG_COMMIT). Run: git checkout $TAG"

# --- Guard: tag is published on origin and part of released main -----------
# Enforces Release Please as the source of truth: reject local-only or forged
# tags that never went through the dev -> main -> Release Please flow.
git fetch --quiet origin main --tags \
  || fail "Unable to fetch origin (network required to verify the release tag)."
# Resolve origin's tag commit (peeled for annotated tags; plain ref for lightweight).
REMOTE_TAG_COMMIT="$(git ls-remote origin "refs/tags/${TAG}^{}" | awk '{print $1}')"
if [ -z "$REMOTE_TAG_COMMIT" ]; then
  REMOTE_TAG_COMMIT="$(git ls-remote origin "refs/tags/${TAG}" | awk '{print $1}')"
fi
[ -n "$REMOTE_TAG_COMMIT" ] \
  || fail "Tag '$TAG' is not published on origin. Only Release Please tags may be deployed."
[ "$REMOTE_TAG_COMMIT" = "$TAG_COMMIT" ] \
  || fail "Local tag '$TAG' ($TAG_COMMIT) does not match origin's tag commit ($REMOTE_TAG_COMMIT). A moved local tag is not deployable; re-fetch tags."
git merge-base --is-ancestor "$TAG_COMMIT" origin/main \
  || fail "Tag '$TAG' ($TAG_COMMIT) is not reachable from origin/main. Deploy only stable releases merged to main."

# --- Guard: config/version.php matches the tag (without leading v) ----------
EXPECTED_VERSION="${TAG#v}"
grep -q "'app' => '${EXPECTED_VERSION}'" config/version.php \
  || fail "config/version.php does not contain version '${EXPECTED_VERSION}'"

SHORT_HASH="$(git rev-parse --short HEAD)"
RELEASE_NAME="${TAG}-${SHORT_HASH}"
RELEASE_DIR="${DEPLOY_ROOT}/releases/${RELEASE_NAME}"

echo "=== Deploying Nova Helpdesk ${TAG} (${SHORT_HASH}) to ${DEPLOY_HOST} ==="

# --- Guard: production shared .env must already exist -----------------------
echo "Verifying provisioned production env..."
ssh "$DEPLOY_HOST" "[ -f '${SHARED_ENV}' ]" \
  || fail "Missing ${SHARED_ENV} on server. Provision it before deploying (see CONTRIBUTING.md)."

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

# --- Step 1: Build assets locally ------------------------------------------
echo "Building assets locally..."
VITE_REVERB_HOST="$REVERB_HOST" \
VITE_REVERB_PORT="$REVERB_PORT" \
VITE_REVERB_SCHEME="$REVERB_SCHEME" \
  pnpm run build

# --- Step 2: Create remote release directory -------------------------------
echo "Creating remote release directory ${RELEASE_DIR}..."
ssh "$DEPLOY_HOST" "mkdir -p '${RELEASE_DIR}'"

# --- Step 3: Sync source + built assets ------------------------------------
echo "Uploading files to VPS..."
rsync -avz --delete \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='.github' \
  --exclude='tests' \
  --exclude='storage' \
  --exclude='public/hot' \
  --exclude='.env' \
  --exclude='.serena' \
  --exclude='.agents' \
  --exclude='.ai' \
  --exclude='.deploy' \
  --exclude='deploy.sh' \
  ./ "${DEPLOY_HOST}:${RELEASE_DIR}/"

# --- Step 4: Remote release tasks ------------------------------------------
echo "Running remote tasks..."
ssh "$DEPLOY_HOST" bash -s -- "$DEPLOY_ROOT" "$RELEASE_DIR" "$KEEP_RELEASES" <<'REMOTE'
set -euo pipefail
DEPLOY_ROOT="$1"
RELEASE_DIR="$2"
KEEP_RELEASES="$3"
SHARED="${DEPLOY_ROOT}/shared"

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

echo "Running composer install..."
composer install --no-dev --optimize-autoloader --no-interaction

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

echo "Updating current symlink..."
ln -sfn "$RELEASE_DIR" "${DEPLOY_ROOT}/current"

# Restart realtime workers so supervisor-spawned processes load the new release.
# Supervisor resolves cwd via the 'current' symlink; long-lived processes (esp.
# reverb, which has no --max-time) otherwise stay pinned to the old release path
# until cleanup deletes it, breaking broadcasting. deployer has scoped,
# password-less sudo for exactly these supervisorctl commands.
echo "Restarting Reverb + queue workers via supervisor..."
sudo supervisorctl restart nova-helpdesk-reverb nova-helpdesk-queue:

echo "Cleaning up old releases (keeping ${KEEP_RELEASES})..."
ls -dt "${DEPLOY_ROOT}/releases/"* | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf
REMOTE

echo "=== Deployment ${TAG} (${SHORT_HASH}) finished successfully! ==="
