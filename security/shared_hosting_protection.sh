#!/bin/bash
# ====================================================
# iSewaProject Shared Hosting Protection Script
# Perlindungan dari "Teman Sebangku" di cPanel Bersama
# ====================================================
# JALANKAN SCRIPT INI DI SERVER HOSTING SEBELUM PRESENTASI!
# bash security/shared_hosting_protection.sh
# ====================================================

set -euo pipefail

PROJECT_DIR=$(dirname "$(dirname "$(readlink -f "$0")")")
DATE=$(date '+%Y-%m-%d %H:%M:%S')

echo "======================================"
echo " iSewaProject Shared Hosting Shield"
echo " $DATE"
echo "======================================"

# ==========================================
# FASE 1: CACHE CONFIG (Sembunyikan .env)
# ==========================================
echo ""
echo "[FASE 1] Menyembunyikan kredensial .env ke dalam cache..."

cd "$PROJECT_DIR"

# Cache semua konfigurasi — setelah ini Laravel TIDAK lagi membaca .env secara langsung
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Config, Route, dan View sudah di-cache."
echo "   Laravel sekarang membaca dari bootstrap/cache/config.php"
echo "   File .env TIDAK lagi diperlukan untuk runtime!"

# ==========================================
# FASE 2: GANTI ISI .ENV DENGAN DATA PALSU (DECOY)
# ==========================================
echo ""
echo "[FASE 2] Membuat .env Decoy (umpan palsu)..."

# Backup .env asli ke lokasi tersembunyi
HIDDEN_ENV="$PROJECT_DIR/storage/.env.real.backup"
cp "$PROJECT_DIR/.env" "$HIDDEN_ENV"
chmod 600 "$HIDDEN_ENV" 2>/dev/null || true

# Timpa .env dengan data palsu
cat > "$PROJECT_DIR/.env" << 'DECOY_ENV'
# ==========================================
# iSewaProject - Environment Configuration
# ==========================================
APP_NAME=iSewaProject
APP_ENV=production
APP_KEY=base64:FAKE_KEY_DO_NOT_USE_THIS_xxxxxxxxxxx=
APP_DEBUG=false
APP_URL=https://isewa.bumdes.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=isewa_dummy_db
DB_USERNAME=readonly_user
DB_PASSWORD=ThisIsNotTheRealPassword123!

SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=fake_username
MAIL_PASSWORD=fake_password
DECOY_ENV

echo "✅ File .env sekarang berisi DATA PALSU (Decoy/Umpan)."
echo "   Jika kelompok lain mengintip, mereka mendapat password PALSU!"
echo ""
echo "   .env asli tersimpan aman di: $HIDDEN_ENV"

# ==========================================
# FASE 3: SET FILE PERMISSIONS
# ==========================================
echo ""
echo "[FASE 3] Mengatur permission file..."

# Sembunyikan file backup .env
chmod 600 "$HIDDEN_ENV" 2>/dev/null || true

# Set production mode
if [ -f "$PROJECT_DIR/bootstrap/cache/config.php" ]; then
    echo "✅ Cache config terdeteksi. Sistem berjalan dari cache."
fi

# ==========================================
# FASE 4: BUAT .htaccess PROTECTION
# ==========================================
echo ""
echo "[FASE 4] Menambahkan .htaccess protection..."

# Tambahkan .htaccess di root project untuk blokir akses ke file sensitif (Apache)
cat > "$PROJECT_DIR/.htaccess" << 'HTACCESS'
# ==========================================
# iSewaProject - Root .htaccess Protection
# Blokir akses langsung ke file sensitif
# ==========================================

# Blokir akses ke .env (asli maupun backup)
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>

# Blokir akses ke file konfigurasi
<FilesMatch "\.(env|log|sql|sqlite|bak|conf|ini|sh|bash)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Blokir akses ke folder tersembunyi
<DirectoryMatch "^\.|\/\.">
    Order allow,deny
    Deny from all
</DirectoryMatch>

# Redirect semua traffic ke folder public/
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
HTACCESS

echo "✅ .htaccess protection aktif."

# ==========================================
# FASE 5: PROTEKSI FOLDER STORAGE
# ==========================================
echo ""
echo "[FASE 5] Melindungi folder storage dari akses web..."

# Pastikan ada .htaccess di storage untuk blokir akses web
cat > "$PROJECT_DIR/storage/.htaccess" << 'STORAGE_HT'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule .* - [F,L]
</IfModule>
STORAGE_HT

echo "✅ Storage folder terlindungi."

# ==========================================
# RINGKASAN
# ==========================================
echo ""
echo "======================================"
echo " ✅ SHARED HOSTING PROTECTION AKTIF!"
echo "======================================"
echo ""
echo " Status:"
echo "   🔒 Config cached     → .env tidak diperlukan runtime"
echo "   🎭 .env Decoy aktif  → Password palsu terpasang"
echo "   📁 .env asli aman    → Tersimpan di storage/"
echo "   🛡️ .htaccess aktif   → File sensitif diblokir"
echo ""
echo " ⚠️  PENTING SEBELUM PRESENTASI:"
echo "   1. Pastikan web berjalan normal setelah cache"
echo "   2. Jangan jalankan 'php artisan config:clear'"
echo "      karena akan membuat Laravel baca .env palsu!"
echo ""
echo " 🔄 UNTUK RESTORE .env ASLI (setelah presentasi):"
echo "   cp $HIDDEN_ENV $PROJECT_DIR/.env"
echo "   php artisan config:clear"
echo "======================================"
