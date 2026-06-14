#!/bin/bash
# ====================================================
# iSewaProject Firewall Hardening Script
# Skenario 26-31: Pertahanan Level Firewall
# ====================================================

echo "===================================="
echo " iSewaProject Firewall Hardening"
echo "===================================="

# --- Skenario 26: Packet Fragmentation Defense ---
echo "\n[Skenario 26] Memasang pertahanan Packet Fragmentation..."
# Install Snort IDS (opsional - membutuhkan resource besar)
# sudo apt-get install -y snort
# Untuk alternatif yang lebih ringan, gunakan aturan iptables:
sudo iptables -A INPUT -f -j DROP
echo "✅ Fragmented packets akan di-drop."

# --- Skenario 27: IP Address Spoofing Defense ---
echo "\n[Skenario 27] Mengaktifkan Reverse Path Filtering..."
sudo sysctl -w net.ipv4.conf.all.rp_filter=1
sudo sysctl -w net.ipv4.conf.default.rp_filter=1
# Buat persisten setelah reboot
if ! grep -q "net.ipv4.conf.all.rp_filter" /etc/sysctl.conf; then
    echo "net.ipv4.conf.all.rp_filter = 1" | sudo tee -a /etc/sysctl.conf
    echo "net.ipv4.conf.default.rp_filter = 1" | sudo tee -a /etc/sysctl.conf
fi
echo "✅ Reverse Path Filtering aktif."

# --- Skenario 28: Port Knocking / Brute Force Defense ---
echo "\n[Skenario 28] Menginstal dan mengkonfigurasi Fail2ban..."
sudo apt-get install -y fail2ban
cat > /tmp/jail.local << 'JAIL_CONF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5
backend = systemd

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 7200

[nginx-http-auth]
enabled = true
port = http,https
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 5

[nginx-limit-req]
enabled = true
port = http,https
filter = nginx-limit-req
logpath = /var/log/nginx/error.log
maxretry = 10
findtime = 120
bantime = 600
JAIL_CONF
sudo mv /tmp/jail.local /etc/fail2ban/jail.local
sudo systemctl enable fail2ban
sudo systemctl restart fail2ban
echo "✅ Fail2ban aktif. SSH maxretry=3, ban 2 jam."

# --- Skenario 29: Source Port Manipulation Defense ---
echo "\n[Skenario 29] UFW sudah stateful secara default."
echo "   Menambahkan aturan tambahan..."
# Drop paket yang invalid
sudo iptables -A INPUT -m state --state INVALID -j DROP
echo "✅ Invalid/spoofed packets akan di-drop."

# --- Skenario 30: SYN Flood Attack Defense ---
echo "\n[Skenario 30] Mengaktifkan SYN Cookies dan proteksi flood..."
sudo sysctl -w net.ipv4.tcp_syncookies=1
sudo sysctl -w net.ipv4.tcp_max_syn_backlog=2048
sudo sysctl -w net.ipv4.tcp_synack_retries=2
sudo sysctl -w net.ipv4.tcp_syn_retries=5
# Buat persisten setelah reboot
if ! grep -q "net.ipv4.tcp_syncookies" /etc/sysctl.conf; then
    cat >> /etc/sysctl.conf << 'SYSCTL'

# iSewaProject SYN Flood Protection
net.ipv4.tcp_syncookies = 1
net.ipv4.tcp_max_syn_backlog = 2048
net.ipv4.tcp_synack_retries = 2
net.ipv4.tcp_syn_retries = 5
SYSCTL
fi
echo "✅ SYN Cookies aktif, SYN flood protection enabled."

# --- Skenario 31: Brute Force SSH Defense ---
echo "\n[Skenario 31] Mengamankan SSH Server..."
SSHD_CONF="/etc/ssh/sshd_config"
if [ -f "$SSHD_CONF" ]; then
    sudo sed -i 's/^#*PermitRootLogin.*/PermitRootLogin no/' "$SSHD_CONF"
    sudo sed -i 's/^#*MaxAuthTries.*/MaxAuthTries 3/' "$SSHD_CONF"
    sudo sed -i 's/^#*PasswordAuthentication.*/PasswordAuthentication yes/' "$SSHD_CONF"
    sudo sed -i 's/^#*LoginGraceTime.*/LoginGraceTime 60/' "$SSHD_CONF"
    sudo sed -i 's/^#*ClientAliveInterval.*/ClientAliveInterval 300/' "$SSHD_CONF"
    sudo sed -i 's/^#*ClientAliveCountMax.*/ClientAliveCountMax 2/' "$SSHD_CONF"
    sudo systemctl restart sshd
    echo "✅ SSH: PermitRootLogin=no, MaxAuthTries=3"
fi

echo "\n===================================="
echo " ✅ Firewall Hardening Selesai!"
echo "===================================="
