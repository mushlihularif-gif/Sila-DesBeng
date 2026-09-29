#!/bin/bash
set -e

echo "Menarik update terbaru dari GitHub..."
git pull origin main

echo "Menyinkronkan aset public ke document root..."
cp -R public/. /home/inon1796/public_html/siladesbeng.inovasia.site/

echo "Menyegarkan cache Laravel..."
php artisan route:clear
php artisan view:clear
php artisan config:clear

echo "Deploy selesai dengan sukses."
