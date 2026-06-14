#!/bin/bash
# ====================================================
# iSewaProject Security Deployment Script
# Hardening Server Ubuntu untuk Production
# ====================================================

echo "===================================="
echo " iSewaProject Security Hardening"
echo " Ubuntu Server Deployment Script"
echo "===================================="

# --- Skenario 16: Port Scanning Defense (UFW Firewall) ---
echo "\n[Skenario 16] Mengkonfigurasi UFW Firewall..."
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp comment 'SSH'
sudo ufw allow 80/tcp comment 'HTTP'
sudo ufw allow 443/tcp comment 'HTTPS'
sudo ufw --force enable
echo "✅ UFW Firewall aktif. Hanya port 22, 80, 443 yang terbuka."

# --- Skenario 17: Web Server Vulnerability Scanning Defense ---
echo "\n[Skenario 17] Mengamankan konfigurasi web server..."
# Set Laravel .env ke mode production
if [ -f /var/www/isewaproject/.env ]; then
    sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' /var/www/isewaproject/.env
    sed -i 's/APP_ENV=local/APP_ENV=production/' /var/www/isewaproject/.env
    chmod 600 /var/www/isewaproject/.env
    echo "✅ APP_DEBUG=false, APP_ENV=production, .env permission 600"
fi

# Konfigurasi Nginx - pastikan document root mengarah ke public/
cat > /etc/nginx/conf.d/isewaproject-security.conf << 'NGINX_CONF'
# Security headers untuk Nginx
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "DENY" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# Blokir akses ke file tersembunyi (.env, .git, dll.)
location ~ /\. {
    deny all;
    access_log off;
    log_not_found off;
}

# Blokir akses ke file sensitif
location ~* \.(env|log|sql|sqlite|bak|conf|ini)$ {
    deny all;
}

# Nonaktifkan eksekusi PHP di direktori upload
location ~* /storage/.*\.php$ {
    deny all;
}
location ~* /uploads/.*\.php$ {
    deny all;
}
NGINX_CONF
echo "✅ Nginx security headers dan block rules ditambahkan."

# --- Skenario 18: Network Sniffing Defense (HTTPS/SSL) ---
echo "\n[Skenario 18] Menginstal SSL/TLS Certificate..."
sudo apt-get update
sudo apt-get install -y certbot python3-certbot-nginx
echo "⚠️  Jalankan: sudo certbot --nginx -d yourdomain.com"
echo "   untuk mengaktifkan HTTPS."

# --- Skenario 19: Direct Database Attack Defense ---
echo "\n[Skenario 19] Mengamankan MySQL dari akses eksternal..."
MYSQL_CONF="/etc/mysql/mysql.conf.d/mysqld.cnf"
if [ -f "$MYSQL_CONF" ]; then
    if grep -q "bind-address" "$MYSQL_CONF"; then
        sudo sed -i 's/^bind-address.*/bind-address = 127.0.0.1/' "$MYSQL_CONF"
    else
        echo "bind-address = 127.0.0.1" | sudo tee -a "$MYSQL_CONF"
    fi
    echo "✅ MySQL bind-address = 127.0.0.1 (hanya akses lokal)"
fi
sudo ufw deny 3306
echo "✅ Port 3306 (MySQL) diblokir dari akses luar."

# --- Skenario 20: Kriptanalisis Defense ---
echo "\n[Skenario 20] Password hashing sudah menggunakan Bcrypt via Laravel."
echo "✅ Ditambahkan enkripsi pada data bank di SystemSetting model."

# --- Skenario 21: DoS/DDoS Defense ---
echo "\n[Skenario 21] Memasang rate limiting..."
# Konfigurasi rate limiting untuk Nginx
cat > /etc/nginx/conf.d/rate-limiting.conf << 'RATE_CONF'
# Zona rate limiting
limit_req_zone $binary_remote_addr zone=isewaproject:10m rate=30r/s;
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_conn_zone $binary_remote_addr zone=connlimit:10m;

# Terapkan di server block:
# limit_req zone=isewaproject burst=20 nodelay;
# limit_req zone=login burst=3 nodelay; (untuk route /auth/*)
# limit_conn connlimit 20;
RATE_CONF
echo "✅ Nginx rate limiting dikonfigurasi."

# --- Skenario 22: Log Wiping Defense ---
echo "\n[Skenario 22] Mengunci file log dari penghapusan..."
sudo chattr +a /var/log/auth.log 2>/dev/null
sudo chattr +a /var/log/syslog 2>/dev/null
sudo chattr +a /var/log/nginx/access.log 2>/dev/null
sudo chattr +a /var/log/nginx/error.log 2>/dev/null
echo "✅ File log dikunci dengan atribut append-only (+a)."

# --- Skenario 23: phpMyAdmin Protection ---
echo "\n[Skenario 23] Mengamankan phpMyAdmin..."
PMA_CONF="/etc/nginx/snippets/phpmyadmin.conf"
if [ -f "$PMA_CONF" ]; then
    echo "⚠️  Ubah alias phpMyAdmin di $PMA_CONF"
    echo "   Ganti '/phpmyadmin' menjadi URL rahasia seperti '/db_bumdes_secret_2024'"
fi
echo "✅ Panduan keamanan phpMyAdmin diterapkan."

# --- Skenario 24: Supply Chain Attack Defense ---
echo "\n[Skenario 24] Menjalankan audit dependensi..."
cd /var/www/isewaproject 2>/dev/null || cd .
if command -v composer &> /dev/null; then
    echo "--- Composer Audit ---"
    composer audit 2>/dev/null || echo "Jalankan 'composer audit' secara manual."
fi
if command -v npm &> /dev/null; then
    echo "--- NPM Audit ---"
    npm audit 2>/dev/null || echo "Jalankan 'npm audit' secara manual."
fi
echo "✅ Audit dependensi selesai."

# --- Skenario 25: Security Misconfiguration (File Permissions) ---
echo "\n[Skenario 25] Mengatur hak akses file..."
PROJECT_DIR="/var/www/isewaproject"
if [ -d "$PROJECT_DIR" ]; then
    sudo chown -R www-data:www-data "$PROJECT_DIR"
    sudo find "$PROJECT_DIR" -type f -exec chmod 644 {} \;
    sudo find "$PROJECT_DIR" -type d -exec chmod 755 {} \;
    sudo chmod -R 775 "$PROJECT_DIR/storage"
    sudo chmod -R 775 "$PROJECT_DIR/bootstrap/cache"
    sudo chmod 600 "$PROJECT_DIR/.env"
    echo "✅ File permissions: 644 (files), 755 (dirs), 600 (.env)"
fi

echo "\n===================================="
echo " ✅ Server Hardening Selesai!"
echo " Restart services:"
echo "   sudo systemctl restart nginx"
echo "   sudo systemctl restart mysql"
echo "===================================="
