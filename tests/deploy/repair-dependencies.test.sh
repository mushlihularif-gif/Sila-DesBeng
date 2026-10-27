#!/usr/bin/env bash
# Isolated workflow test: no PHP packages, network access, or production data.
set -Eeuo pipefail
ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../.." && pwd -P)"
FIXTURE="$(mktemp -d)"
mkdir -p "$FIXTURE/bin"
cat >"$FIXTURE/bin/flock" <<'SH'
#!/usr/bin/env bash
exit 0
SH
cat >"$FIXTURE/bin/php" <<'SH'
#!/usr/bin/env bash
set -eu
if [ "${1:-}" = '-d' ]; then shift 2; fi
if [ "${1:-}" = '-r' ]; then exit 0; fi
shift
action="${1:-}"
if [ "$action" = install ]; then
    stage=''
    for arg in "$@"; do
        case "$arg" in --working-dir=*) stage="${arg#*=}" ;; esac
    done
    test -n "$stage"
    mkdir -p "$stage/vendor/composer" "$stage/vendor/laravel/framework/src/Illuminate/Foundation"
    [ "${MOCK_FAIL_INSTALL:-0}" -eq 0 ] || exit 7
    printf '{}\n' >"$stage/vendor/composer/installed.json"
    touch "$stage/vendor/laravel/framework/src/Illuminate/Foundation/Application.php"
elif [ "$action" = dump-autoload ]; then
    touch vendor/autoload.php
elif [ "$action" = '--version' ]; then
    printf 'Laravel Framework test-fixture\n'
fi
SH
chmod +x "$FIXTURE/bin/php" "$FIXTURE/bin/flock"

for scenario in success failed-download; do
    project="$FIXTURE/$scenario"
    mkdir -p "$project/deploy" "$project/storage/framework" "$project/bootstrap/cache" "$project/vendor/google/apiclient-services"
    cp "$ROOT/deploy/repair-dependencies.sh" "$project/deploy/"
    touch "$project/artisan" "$project/composer.json" "$project/composer.lock" "$project/composer.phar"
    printf 'locally modified dependency\n' >"$project/vendor/google/apiclient-services/preserved.txt"
    printf 'private compiled config\n' >"$project/bootstrap/cache/config.php"
    fail=0
    [ "$scenario" != failed-download ] || fail=1
    if PATH="$FIXTURE/bin:$PATH" SEGIDAERAH_PHP="$FIXTURE/bin/php" \
        SEGIDAERAH_COMPOSER="$project/composer.phar" SEGIDAERAH_RECOVERY_ROOT="$project/backups" \
        MOCK_FAIL_INSTALL="$fail" bash "$project/deploy/repair-dependencies.sh" >"$project/output.log" 2>&1; then
        test "$scenario" = success
        test -f "$project/vendor/autoload.php"
        test ! -f "$project/vendor/google/apiclient-services/preserved.txt"
        test "$(find "$project/backups" -path '*/vendor-before/google/apiclient-services/preserved.txt' | wc -l)" -eq 1
        test "$(find "$project/backups" -path '*/cache-before/config.php' | wc -l)" -eq 1
    else
        test "$scenario" = failed-download
        test -f "$project/vendor/google/apiclient-services/preserved.txt"
        test ! -f "$project/vendor/autoload.php"
        test "$(find "$project/backups" -name vendor-before | wc -l)" -eq 0
        test "$(find "$project/backups" -name install | wc -l)" -eq 0
    fi
    printf 'PASS: %s\n' "$scenario"
done
printf 'Disposable fixtures retained outside the repository: %s\n' "$FIXTURE"
