#!/usr/bin/env bash
set -euo pipefail

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
export DEPLOY_TEST_MODE=1
# shellcheck source=../../deploy.sh
source "${ROOT}/deploy.sh"

TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT

assert_fails() {
  local expected=$1
  shift
  local output
  set +e
  output=$(set -e; "$@" 2>&1)
  local rc=$?
  set -e
  if [ "$rc" -eq 0 ]; then
    echo "Expected failure: $*" >&2
    exit 1
  fi
  command grep -Fq "$expected" <<<"$output"
}

echo "MARK: entering case 1" >&2
# --- 1. Basic stable tag validation ---
assert_fails "not a stable release tag" validate_stable_tag v1.2.3-rc.1
validate_stable_tag v1.2.3

echo "MARK: entering case 2" >&2
# --- 2. Archive exclusions & checksum integrity ---
SOURCE="$TMP/source"
mkdir -p "$SOURCE/public/build" "$SOURCE/vendor" "$SOURCE/tests" "$SOURCE/node_modules"
printf 'app' > "$SOURCE/artisan"
printf 'asset' > "$SOURCE/public/build/manifest.json"
printf 'vendor' > "$SOURCE/vendor/autoload.php"
printf 'exclude' > "$SOURCE/tests/example.php"
printf 'exclude' > "$SOURCE/node_modules/example.js"

BUNDLE_DIR="$TMP/bundles"
mkdir -p "$BUNDLE_DIR"
create_archive "$SOURCE" "v1.2.3-abc123" "$BUNDLE_DIR"
tar -tzf "$BUNDLE_DIR/v1.2.3-abc123.tar.gz" > "$TMP/files"

grep -Fq './vendor/autoload.php' "$TMP/files"
grep -Fq './public/build/manifest.json' "$TMP/files"
! grep -Fq './tests/' "$TMP/files"
! grep -Fq './node_modules/' "$TMP/files"
(cd "$BUNDLE_DIR" && sha256sum --check v1.2.3-abc123.sha256)

# Checksum failure test
(
  TEST_BUNDLE_DIR="$TMP/checksum_test"
  mkdir -p "$TEST_BUNDLE_DIR"
  create_archive "$SOURCE" "v1.2.3-checksum" "$TEST_BUNDLE_DIR"
  echo "corrupted" >> "$TEST_BUNDLE_DIR/v1.2.3-checksum.tar.gz"
  assert_fails "FAILED" sh -c "cd $TEST_BUNDLE_DIR && sha256sum --check v1.2.3-checksum.sha256"
)

echo "MARK: entering case 3" >&2
# --- 3. Locking ---
LOCK_ROOT="$TMP/remote"
mkdir -p "$LOCK_ROOT/shared" "$LOCK_ROOT/releases/old" "$LOCK_ROOT/releases/new"
ln -s "$LOCK_ROOT/releases/old" "$LOCK_ROOT/current"

acquire_deploy_lock "$LOCK_ROOT"
assert_fails "another deployment is active" acquire_deploy_lock "$LOCK_ROOT"
release_deploy_lock "$LOCK_ROOT"

echo "MARK: entering case 4" >&2
# --- 4. Rollback on health check failure with existing release ---
(
  restart_workers() { :; }
  health_check() { return 1; }
  activate_release "$LOCK_ROOT" "$LOCK_ROOT/releases/new" || true
  test "$(readlink "$LOCK_ROOT/current")" = "$LOCK_ROOT/releases/old"
)

echo "MARK: entering case 5" >&2
# --- 5. Rollback on worker restart failure with existing release ---
(
  restart_workers() { return 1; }
  health_check() { return 0; }
  assert_fails "health check or worker restart failed; restored previous release" activate_release "$LOCK_ROOT" "$LOCK_ROOT/releases/new"
  test "$(readlink "$LOCK_ROOT/current")" = "$LOCK_ROOT/releases/old"
)

echo "MARK: entering case 6" >&2
# --- 6. First deploy failure (no previous release) ---
(
  FIRST_DEPLOY_ROOT="$TMP/first_deploy"
  mkdir -p "$FIRST_DEPLOY_ROOT/releases/new"
  rm -f "$FIRST_DEPLOY_ROOT/current"
  restart_workers() { :; }
  health_check() { return 1; }
  assert_fails "no previous release to restore" activate_release "$FIRST_DEPLOY_ROOT" "$FIRST_DEPLOY_ROOT/releases/new"
  if [ -L "$FIRST_DEPLOY_ROOT/current" ] || [ -e "$FIRST_DEPLOY_ROOT/current" ]; then
    echo "Error: current symlink should have been deleted on first deploy failure!" >&2
    exit 1
  fi
)

# Get the valid tag version from config/version.php
ACTUAL_VERSION=$(command grep -oE "[0-9]+\.[0-9]+\.[0-9]+" "${ROOT}/config/version.php" | head -n 1)
VALID_TAG="v${ACTUAL_VERSION}"

# Define mock environment for guard tests
export DEPLOY_HOST="test-host"
export DEPLOY_ROOT="$TMP/remote_guard"
export REVERB_HOST="test-reverb"
export HEALTHCHECK_URL="http://localhost/up"

echo "MARK: entering case 7" >&2
# --- 7. Dirty worktree guard test ---
(
  git() {
    if [ "$*" = "status --porcelain" ]; then
      echo "M modified.txt"
      return 0
    fi
    command git "$@"
  }
  assert_fails "Working tree is dirty" main "$VALID_TAG"
)

echo "MARK: entering case 8" >&2
# --- 8. Missing/unreachable tags guard tests ---
(
  # Tag does not exist locally
  git() {
    if [ "$*" = "status --porcelain" ]; then
      return 0
    fi
    if [[ "$*" == *"rev-parse -q --verify refs/tags/"* ]]; then
      return 1
    fi
    command git "$@"
  }
  assert_fails "does not exist locally" main "$VALID_TAG"
)

(
  # Tag not on origin
  git() {
    case "$*" in
      "status --porcelain")
        return 0
        ;;
      "fetch --quiet origin main --tags")
        return 0
        ;;
      "ls-remote origin refs/tags/"*|"ls-remote origin refs/tags/"*"^{}")
        return 0
        ;;
      "rev-parse -q --verify refs/tags/"*)
        return 0
        ;;
      "rev-list -n 1 "*|"rev-parse HEAD")
        echo "abc123commit"
        return 0
        ;;
      *)
        command git "$@"
        ;;
    esac
  }
  assert_fails "is not published on origin" main "$VALID_TAG"
)

(
  # Tag is not reachable from origin/main
  git() {
    case "$*" in
      "status --porcelain")
        return 0
        ;;
      "fetch --quiet origin main --tags")
        return 0
        ;;
      "ls-remote origin refs/tags/"*|"ls-remote origin refs/tags/"*"^{}")
        echo "abc123commit refs/tags/${VALID_TAG}"
        return 0
        ;;
      "rev-parse -q --verify refs/tags/"*)
        return 0
        ;;
      "rev-list -n 1 "*|"rev-parse HEAD")
        echo "abc123commit"
        return 0
        ;;
      "merge-base --is-ancestor abc123commit origin/main")
        return 1
        ;;
      *)
        command git "$@"
        ;;
    esac
  }
  assert_fails "is not reachable from origin/main" main "$VALID_TAG"
)

echo "MARK: entering case 9" >&2
# --- 9. Version mismatch guard test ---
(
  grep() {
    if [[ "$*" == *"config/version.php"* ]]; then
      return 1
    fi
    command grep "$@"
  }
  git() {
    case "$*" in
      "status --porcelain")
        return 0
        ;;
      "fetch --quiet origin main --tags")
        return 0
        ;;
      "ls-remote origin refs/tags/"*|"ls-remote origin refs/tags/"*"^{}")
        echo "abc123commit refs/tags/${VALID_TAG}"
        return 0
        ;;
      "rev-parse -q --verify refs/tags/"*)
        return 0
        ;;
      "rev-list -n 1 "*|"rev-parse HEAD")
        echo "abc123commit"
        return 0
        ;;
      "merge-base --is-ancestor abc123commit origin/main")
        return 0
        ;;
      *)
        command git "$@"
        ;;
    esac
  }
  assert_fails "config/version.php does not contain version" main "$VALID_TAG"
)

echo "MARK: entering case 10" >&2
# --- 10. Dry-run execution test ---
(
  # Mock git to pass all local checks
  git() {
    case "$*" in
      "status --porcelain")
        return 0
        ;;
      "fetch --quiet origin main --tags")
        return 0
        ;;
      "ls-remote origin refs/tags/"*|"ls-remote origin refs/tags/"*"^{}")
        echo "abc123commit refs/tags/${VALID_TAG}"
        return 0
        ;;
      "rev-parse -q --verify refs/tags/"*)
        return 0
        ;;
      "rev-list -n 1 "*|"rev-parse HEAD")
        echo "abc123commit"
        return 0
        ;;
      "merge-base --is-ancestor abc123commit origin/main")
        return 0
        ;;
      *)
        command git "$@"
        ;;
    esac
  }
  
  # Mock create_release_bundle to avoid actual building/composer install
  create_release_bundle() {
    local tag=$1
    local tag_commit=$2
    local output_dir=$3
    local short_hash
    short_hash="abc123c"
    RELEASE_NAME="${tag}-${short_hash}"
    # Create mock bundle files
    touch "${output_dir}/${RELEASE_NAME}.tar.gz"
    touch "${output_dir}/${RELEASE_NAME}.sha256"
  }

  ssh() {
    echo "Error: ssh should not be called in dry-run mode!" >&2
    exit 1
  }
  rsync() {
    echo "Error: rsync should not be called in dry-run mode!" >&2
    exit 1
  }

  # Run main in dry-run mode and check that it exits with 0
  main "$VALID_TAG" "--dry-run"
)

echo "deploy tests passed"
