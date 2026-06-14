#!/bin/bash
# ====================================================
# iSewaProject Automated Backup & Disaster Recovery
# Skenario Keamanan: Zero-Day & Ransomware Protection
# ====================================================
# Cron Schedule: Jalankan setiap malam jam 02:00 WIB
# 0 2 * * * /var/www/isewaproject/security/backup_database.sh >> /var/log/isewaproject-backup.log 2>&1
# ====================================================

set -euo pipefail

# ==========================================
# KONFIGURASI
# ==========================================
PROJECT_DIR="/var/www/isewaproject"
BACKUP_DIR="/var/backups/isewaproject"
CLOUD_BACKUP_DIR="/var/backups/isewaproject/cloud-queue"
DB_NAME="isewa_project_security"
DB_USER="root"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
RETENTION_DAYS=30
LOG_PREFIX="[iSewa-Backup $(date '+%Y-%m-%d %H:%M:%S')]"

echo "$LOG_PREFIX ======================================"
echo "$LOG_PREFIX  AUTOMATED BACKUP & DISASTER RECOVERY"
echo "$LOG_PREFIX ======================================"

# ==========================================
# FASE 1: BACKUP DATABASE (MySQL Dump)
# ==========================================
echo "$LOG_PREFIX [FASE 1] Memulai backup database..."

sudo mkdir -p "$BACKUP_DIR"
sudo mkdir -p "$CLOUD_BACKUP_DIR"
sudo chmod 700 "$BACKUP_DIR"

DB_BACKUP_FILE="$BACKUP_DIR/db_${DATE}.sql.gz"

# Dump database dengan kompresi gzip
if sudo mysqldump -u "$DB_USER" --single-transaction --routines --triggers "$DB_NAME" 2>/dev/null | gzip > "$DB_BACKUP_FILE"; then
    DB_SIZE=$(du -sh "$DB_BACKUP_FILE" | cut -f1)
    echo "$LOG_PREFIX ✅ Database backup berhasil: $DB_BACKUP_FILE ($DB_SIZE)"
else
    echo "$LOG_PREFIX ❌ GAGAL backup database!"
    exit 1
fi

# ==========================================
# FASE 2: BACKUP FILE UPLOAD & STORAGE
# ==========================================
echo "$LOG_PREFIX [FASE 2] Memulai backup file storage..."

STORAGE_BACKUP_FILE="$BACKUP_DIR/storage_${DATE}.tar.gz"

if [ -d "$PROJECT_DIR/storage/app/public" ]; then
    sudo tar -czf "$STORAGE_BACKUP_FILE" -C "$PROJECT_DIR" storage/app/public 2>/dev/null
    STORAGE_SIZE=$(du -sh "$STORAGE_BACKUP_FILE" | cut -f1)
    echo "$LOG_PREFIX ✅ Storage backup berhasil: $STORAGE_BACKUP_FILE ($STORAGE_SIZE)"
else
    echo "$LOG_PREFIX ⚠️  Folder storage/app/public tidak ditemukan. Skip."
fi

# ==========================================
# FASE 3: BACKUP FILE .ENV (Kredensial)
# ==========================================
echo "$LOG_PREFIX [FASE 3] Memulai backup konfigurasi .env..."

ENV_BACKUP_FILE="$BACKUP_DIR/env_${DATE}.enc"

if [ -f "$PROJECT_DIR/.env" ]; then
    # Enkripsi file .env dengan openssl sebelum backup
    sudo openssl enc -aes-256-cbc -salt -pbkdf2 \
        -in "$PROJECT_DIR/.env" \
        -out "$ENV_BACKUP_FILE" \
        -pass pass:"$(cat $PROJECT_DIR/.env | grep APP_KEY | cut -d= -f2)" 2>/dev/null
    echo "$LOG_PREFIX ✅ .env backup berhasil (terenkripsi AES-256)"
else
    echo "$LOG_PREFIX ⚠️  File .env tidak ditemukan. Skip."
fi

# ==========================================
# FASE 4: KUNCI FILE BACKUP (IMMUTABLE)
# Anti-Ransomware: File tidak bisa dienkripsi/dihapus
# ==========================================
echo "$LOG_PREFIX [FASE 4] Mengunci file backup (Immutable Protection)..."

sudo chattr +i "$DB_BACKUP_FILE" 2>/dev/null && \
    echo "$LOG_PREFIX ✅ Database backup LOCKED (chattr +i)"

if [ -f "$STORAGE_BACKUP_FILE" ]; then
    sudo chattr +i "$STORAGE_BACKUP_FILE" 2>/dev/null && \
        echo "$LOG_PREFIX ✅ Storage backup LOCKED (chattr +i)"
fi

if [ -f "$ENV_BACKUP_FILE" ]; then
    sudo chattr +i "$ENV_BACKUP_FILE" 2>/dev/null && \
        echo "$LOG_PREFIX ✅ .env backup LOCKED (chattr +i)"
fi

# ==========================================
# FASE 5: UPLOAD KE CLOUD STORAGE (Offsite)
# Disaster Recovery: Backup di lokasi terpisah
# ==========================================
echo "$LOG_PREFIX [FASE 5] Menyiapkan upload ke cloud storage..."

# Copy file ke cloud-queue untuk di-sync oleh rclone/s3cmd
sudo cp "$DB_BACKUP_FILE" "$CLOUD_BACKUP_DIR/" 2>/dev/null

# Jika rclone terinstal, upload ke Google Drive / S3
if command -v rclone &> /dev/null; then
    rclone copy "$CLOUD_BACKUP_DIR/" remote:isewaproject-backups/ --progress 2>/dev/null && \
        echo "$LOG_PREFIX ✅ Cloud upload berhasil (rclone)" || \
        echo "$LOG_PREFIX ⚠️  Cloud upload gagal. File tetap tersimpan lokal."
else
    echo "$LOG_PREFIX ⚠️  rclone tidak terinstal. Install dengan: sudo apt install rclone"
    echo "$LOG_PREFIX    Lalu konfigurasi: rclone config (pilih Google Drive / AWS S3)"
    echo "$LOG_PREFIX    File backup tersimpan di: $CLOUD_BACKUP_DIR/"
fi

# ==========================================
# FASE 6: ROTASI BACKUP (Hapus > 30 Hari)
# ==========================================
echo "$LOG_PREFIX [FASE 6] Membersihkan backup lama (> $RETENTION_DAYS hari)..."

# Harus hapus immutable flag dulu sebelum bisa delete
DELETED_COUNT=$(sudo find "$BACKUP_DIR" -type f \( -name "*.sql.gz" -o -name "*.tar.gz" -o -name "*.enc" \) -mtime +$RETENTION_DAYS 2>/dev/null | wc -l)

if [ "$DELETED_COUNT" -gt 0 ]; then
    sudo find "$BACKUP_DIR" -type f \( -name "*.sql.gz" -o -name "*.tar.gz" -o -name "*.enc" \) \
        -mtime +$RETENTION_DAYS -exec chattr -i {} \; -exec rm -f {} \;
    echo "$LOG_PREFIX ✅ $DELETED_COUNT file backup lama dihapus."
else
    echo "$LOG_PREFIX ✅ Tidak ada backup lama yang perlu dihapus."
fi

# ==========================================
# FASE 7: INTEGRITY CHECK (Verifikasi Hash)
# ==========================================
echo "$LOG_PREFIX [FASE 7] Membuat checksum integritas..."

CHECKSUM_FILE="$BACKUP_DIR/checksums_${DATE}.sha256"
sha256sum "$DB_BACKUP_FILE" > "$CHECKSUM_FILE"
[ -f "$STORAGE_BACKUP_FILE" ] && sha256sum "$STORAGE_BACKUP_FILE" >> "$CHECKSUM_FILE"
[ -f "$ENV_BACKUP_FILE" ] && sha256sum "$ENV_BACKUP_FILE" >> "$CHECKSUM_FILE"

echo "$LOG_PREFIX ✅ Checksum SHA-256 tersimpan: $CHECKSUM_FILE"

# ==========================================
# RINGKASAN
# ==========================================
TOTAL_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
TOTAL_FILES=$(find "$BACKUP_DIR" -type f | wc -l)

echo ""
echo "$LOG_PREFIX ======================================"
echo "$LOG_PREFIX  ✅ BACKUP SELESAI!"
echo "$LOG_PREFIX  Total file backup : $TOTAL_FILES files"
echo "$LOG_PREFIX  Total ukuran      : $TOTAL_SIZE"
echo "$LOG_PREFIX  Retensi           : $RETENTION_DAYS hari"
echo "$LOG_PREFIX  Lokasi            : $BACKUP_DIR"
echo "$LOG_PREFIX ======================================"
echo ""
echo "$LOG_PREFIX PETUNJUK RESTORE:"
echo "$LOG_PREFIX   1. Unlock:  sudo chattr -i /var/backups/isewaproject/db_XXXX.sql.gz"
echo "$LOG_PREFIX   2. Extract: gunzip /var/backups/isewaproject/db_XXXX.sql.gz"
echo "$LOG_PREFIX   3. Restore: mysql -u root isewa_project_security < db_XXXX.sql"
echo "$LOG_PREFIX   4. .env:    openssl enc -aes-256-cbc -d -pbkdf2 -in env_XXXX.enc -out .env"
