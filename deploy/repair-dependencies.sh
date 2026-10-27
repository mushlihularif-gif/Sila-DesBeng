#!/usr/bin/env bash
# Rebuild production dependencies without discarding edits in the old vendor.
set -Eeuo pipefail

APP_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd -P)"
PHP_BIN="${SEGIDAERAH_PHP:-/opt/alt/php82/usr/bin/php}"
COMPOSER_BIN="${SEGIDAERAH_COMPOSER:-$HOME/bin/composer}"
RECOVERY_ROOT="${SEGIDAERAH_RECOVERY_ROOT:-$HOME/segidaerah-recovery}"
RUN_DIR=""
STAGE_DIR=""
SWAPPED=0

report_failure() {
    result=$?
    if [ "$result" -ne 0 ]; then
        printf '\nPemulihan berhenti (kode %s).\n' "$result" >&2
        if [ "$SWAPPED" -eq 0 ]; then
            printf 'Vendor aktif belum diganti oleh skrip ini.\n' >&2
            # Only this run's generated staging data is disposable. Never clean
            # vendor-before, cache-before, or a different recovery run here.
            if [ -n "$RUN_DIR" ] && [ "$STAGE_DIR" = "$RUN_DIR/install" ] && [ -d "$STAGE_DIR" ] && [ ! -L "$STAGE_DIR" ]; then
                rm -rf -- "$STAGE_DIR"
                printf 'Berkas instalasi sementara yang gagal sudah dibersihkan.\n' >&2
            fi
        else
            printf 'Vendor baru sudah dipasang; tahap berikutnya gagal. Salinan vendor sebelumnya: %s/vendor-before\n' "$RUN_DIR" >&2
        fi
        [ -z "$RUN_DIR" ] || printf 'Berkas pemeriksaan tetap disimpan di: %s\n' "$RUN_DIR" >&2
    fi
}
trap report_failure EXIT

cd -- "$APP_DIR"
test -x "$PHP_BIN"
test -f "$COMPOSER_BIN"
test -f composer.lock
test -f artisan
command -v flock >/dev/null
exec 9>storage/framework/dependency-repair.lock
if ! flock -n 9; then
    printf 'Pemulihan lain masih berjalan. Tunggu sampai selesai.\n' >&2
    exit 1
fi

# Retain every other disabled function. This override applies only to Composer.
COMPOSER_DISABLED="$("$PHP_BIN" -r '
$disabled = array_map("trim", explode(",", ini_get("disable_functions")));
$needed = ["proc_open", "proc_get_status", "proc_close", "proc_terminate"];
echo implode(",", array_diff($disabled, $needed));
')"
composer_run() {
    "$PHP_BIN" -d "disable_functions=$COMPOSER_DISABLED" "$COMPOSER_BIN" "$@"
}
"$PHP_BIN" -d "disable_functions=$COMPOSER_DISABLED" -r '
foreach (["proc_open", "proc_get_status", "proc_close", "proc_terminate"] as $function) {
    if (!function_exists($function)) { fwrite(STDERR, "Fungsi CLI tidak tersedia: $function\n"); exit(1); }
}
'

mkdir -p -- "$RECOVERY_ROOT"
RUN_DIR="$(mktemp -d "$RECOVERY_ROOT/run-$(date +%Y%m%d-%H%M%S)-XXXXXX")"
STAGE_DIR="$RUN_DIR/install"
mkdir -- "$STAGE_DIR"
cp -- composer.json composer.lock "$STAGE_DIR/"
printf 'Memasang paket sesuai lockfile di folder terpisah: %s\n' "$STAGE_DIR"

# No project code, plugins, or hooks run in the staging directory.
composer_run install --working-dir="$STAGE_DIR" --no-dev --prefer-dist \
    --no-autoloader --no-scripts --no-plugins --no-interaction
composer_run check-platform-reqs --working-dir="$STAGE_DIR" --no-dev --no-plugins
test -f "$STAGE_DIR/vendor/composer/installed.json"
test -f "$STAGE_DIR/vendor/laravel/framework/src/Illuminate/Foundation/Application.php"

# Preserve compiled caches privately before rebuilding them. They may contain secrets.
cp -a -- bootstrap/cache "$RUN_DIR/cache-before"
if [ -e vendor ]; then
    mv -- vendor "$RUN_DIR/vendor-before"
fi
if ! mv -- "$STAGE_DIR/vendor" vendor; then
    if [ -d "$RUN_DIR/vendor-before" ]; then
        mv -- "$RUN_DIR/vendor-before" vendor
    fi
    exit 1
fi
SWAPPED=1

# Generate paths in the real application directory, after the move.
composer_run dump-autoload --no-dev --optimize --no-scripts --no-plugins --no-interaction

# Equivalent cache cleanup to Laravel's ComposerScripts hook. Do this before
# booting Artisan, since cached providers can refer to removed dev packages.
"$PHP_BIN" -r '
require "vendor/autoload.php";
$app = new Illuminate\Foundation\Application(getcwd());
foreach ([$app->getCachedConfigPath(), $app->getCachedServicesPath(), $app->getCachedPackagesPath()] as $path) {
    if (is_file($path) && !unlink($path)) { fwrite(STDERR, "Cache bootstrap gagal dibersihkan.\n"); exit(1); }
}
'
"$PHP_BIN" artisan package:discover --ansi
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache
"$PHP_BIN" artisan view:cache
"$PHP_BIN" artisan --version
printf '\nPemulihan dependensi selesai. Vendor sebelumnya tersimpan di: %s/vendor-before\n' "$RUN_DIR"
printf 'Refresh Log Keamanan untuk memastikan versi worker web ikut berubah.\n'
