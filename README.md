
# iSewaProject

Perkembangan terhadap project Laravel ini akan didokumentasikan disini!

### _Sebagai Kontributor kami, Kalian Harus Setup untuk Pertama kali_ :
- Clone Repository ini
- Install Dependensi Project yang diperlukan
```bash
  composer create-project Laravel/Laravel iSewaProject
  cd iSewaProject
```
- Membuat file```.env ``` secara manual di Project nya, untuk referensi bisa mengambil di file ```.env.example ```
- Sesuaikan salah satu bagian di file ```.env ``` Dengan catatan Username dan Password boleh default, atau sesuai koneksi masing-masing.
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=isewaproject
```
- Buka terminal lalu jalankan 
```bash
  php artisan key:generate
```
- Jika langkah di atas udah terpenuhi, tetapi masih kendala. Maka, hubungi secara personal di RL.
## Keuntungan memakai Github
- Kita semua bisa saling pull dan push untuk membantu kita semua menyelasikan project yang sangat berharga ini :v

## Version 0.1.1

- Penambahan Template Admin 
- Asset Template admin di public/admin/
- Penambahan Resources/Views/ untuk admin
- Serta menambahkan Routes dan Controller apa adanya untuk admin

Catatan yang perlu diperbaiki :
-
- Error Update di Controllers/DashboardController.php
- Ini menggunakan dependensi public atau mengambil dari sumber luar jadi ada kendala bagi yang mau clone-nya
- Masih Error Index untuk Profil Admin

## Version 0.2.5

- Penambahan model
- Penambahan database/migrations
- Pengembangan layout admin
- Pemecahan index.blade.php nya admin
- Catatan klo mau mengembangkan folder nya lebih lanjut sesuaikan di ```routes\web.php ``` dan ```layouts\admin.blade.php ``` untuk membuat Controllernya.

## Version 0.3.1
- Pembuatan bagian User (baru beranda, belum di pecah)
- ``` npm run dev ``` Jalankan ini untuk lihat user

## Version 0.3.2
- Pemecahan bagian User Beranda
- ```npm run dev ``` Jalankan ini untuk lihat user

## Version 0.4.5
- Advance Fitur Admin
- Penambahan Fitur Login (Beta)
- Jembatan Login Terintegrasi User dan Admin

Catatan yang perlu diperbaikii:
- Belum dilengkapi middleware dan bootsrap/api.php
- Belum bisa reset password dan login akun google , fb , dll
- Memilih ``` php artisan migrate ``` untuk keep datamu atau ``` php artisan migrate:fresh ``` + ``` php artisan db:seed``` untuk dapat akun sampelnya
- ```user@test.com``` password123
- ```admin@isewa.com``` admin123

## Version 0.4.6
- Fix Minor
- Fix Minor Space Kosong
- Navigasi Footer Smooth (Beta) 

## Version 0.5.0
- Penambahan Fitur Bumdes
- Navigasi yang ga terduga interaktifnya
- Sudah bisa update profile dan login (Beta)

## Version 0.6.0
- Major Update
- Penambahan Middleware Guard untuk Keamanan

## Version 0.7.0
- Prequisites untuk mencoba versi ini
```bash
php artisan storage:link
php artisan migrate
```
- Fix Profile Picture Mechanism Both User or Admin
- Ubah Kata Sandi di profile Admin udah Bekerja
- Fix Bug yang terjadi pada Admin/Penyewaan Alat dan Admin/Penjualan Gas
- NEW! Transaksi Unlocked (Beta)

## Version 0.8.0
- Unlock Feature Login With Google + Seamless Integration Between User & Admin
- Fix Transaksi to Stable Version

## Version 0.9.0
- New! Unlock Fitur OTP via E-Mail
- Penyempurnaan beberapa fitur yang sudah ada
- Prequisites untuk mencoba versi ini
```bash
php artisan optimize:clear
```