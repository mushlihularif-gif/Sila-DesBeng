<?php

namespace App\Services;

use App\Models\RentalBooking;
use App\Models\GasOrder;
use Illuminate\Support\Facades\Storage;

class ReceiptGeneratorService
{
    /**
     * Buat bukti transaksi untuk pemesanan penyewaan
     */
    public function generateRentalReceipt(RentalBooking $booking)
    {
        // Muat template latar belakang
        $backgroundPath = public_path('Admin/img/buktitransaksi/buktisewaalatqr.png');
        
        if (!file_exists($backgroundPath)) {
            throw new \Exception('Background template not found: ' . $backgroundPath);
        }

        $temp = imagecreatefrompng($backgroundPath);
        $image = imagecreatetruecolor(imagesx($temp), imagesy($temp));
        imagecopy($image, $temp, 0, 0, 0, 0, imagesx($temp), imagesy($temp));
        imagedestroy($temp);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        
        // Atur warna
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 170, 0);
        
        // Jalur font
        $fontPath = public_path('fonts/arial.ttf');
        
        // Ukuran font
        $normalSize = 24;
        $headerSize = 28;
        
        // Konfigurasi tata letak
        $startY = 400;      // Posisi mulai lebih rendah untuk membersihkan area logo
        $lineHeight = 55;   // Tinggi baris ditingkatkan untuk keterbacaan yang lebih baik
        $labelX = 130;      // Margin kiri untuk label (ditingkatkan untuk jarak "2 jari")
        $valueX = 500;      // Margin kiri untuk nilai (titik dua sejajar)
        
        // No. Pesanan
        $y = $startY;
        $this->addText($image, 'No. Pesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->order_number, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Waktu Pemesanan
        $y += $lineHeight;
        $this->addText($image, 'Waktu Pemesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->created_at->locale('id')->isoFormat('dddd, DD MMMM YYYY  HH:mm') . ' WIB', $valueX, $y, $normalSize, $black, $fontPath);
        
        // Nama Pemesan
        $y += $lineHeight;
        $this->addText($image, 'Nama Akun Pemesan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->user->name, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Email
        $y += $lineHeight;
        $this->addText($image, 'Email Akun Pemesan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->user->email, $valueX, $y, $normalSize, $black, $fontPath);

        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Nama dan Alamat Penyewa
        $y += 70;
        $this->addText($image, 'Nama dan Alamat Penyewa', $labelX, $y, $headerSize, $black, $fontPath, true);

        // Nama Lengkap
        $y += 85;
        $this->addText($image, 'Nama Lengkap', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($booking->recipient_name ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);

        // Alamat
        $y += $lineHeight;
        $this->addText($image, 'Alamat', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($booking->delivery_address ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);

        // Metode
        $y += $lineHeight;
        $this->addText($image, 'Metode', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ucfirst($booking->delivery_method), $valueX, $y, $normalSize, $black, $fontPath);

        // Tujuan Sewa
        $y += $lineHeight;
        $this->addText($image, 'Tujuan Sewa', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($booking->rental_purpose ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);
        
        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Informasi Pembayaran
        $y += 70;
        $this->addText($image, 'Informasi Pembayaran', $labelX, $y, $headerSize, $black, $fontPath, true);
        
        // Waktu Pembayaran
        $y += 85;
        $this->addText($image, 'Waktu Pembayaran', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($booking->confirmed_at ? $booking->confirmed_at->locale('id')->isoFormat('dddd, DD MMMM YYYY  HH:mm') . ' WIB' : '-'), $valueX, $y, $normalSize, $black, $fontPath);
        
        // Metode Pembayaran
        $y += $lineHeight;
        $this->addText($image, 'Metode Pembayaran', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $this->getPaymentMethodLabel($booking->payment_method), $valueX, $y, $normalSize, $black, $fontPath);
        
        // Total Pembayaran
        $y += $lineHeight;
        $this->addText($image, 'Total Pembayaran', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': Rp. ' . number_format($booking->total_amount, 0, ',', '.'), $valueX, $y, $normalSize, $black, $fontPath);
        
        // Status
        $y += $lineHeight;
        $this->addText($image, 'Status', $labelX, $y, $normalSize, $black, $fontPath, true);
        $statusText = $this->determineStatusLabel($booking);
        
        // Tentukan warna status
        $statusColor = $black;
        if (in_array($booking->status, ['completed', 'approved', 'confirmed', 'paid', 'arrived', 'returned'])) {
            $statusColor = $green;
        } elseif (in_array($booking->status, ['cancelled', 'rejected']) || ($booking->cancellation_status ?? '') === 'pending') {
            $statusColor = $red;
        }

        $this->addText($image, ': ' . $statusText, $valueX, $y, $normalSize, $statusColor, $fontPath);
        
        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Detail Pembayaran
        $y += 70;
        $this->addText($image, 'Detail Pembayaran', $labelX, $y, $headerSize, $black, $fontPath, true);
        
        // Header Tabel - Spasi disesuaikan
        $y += 85;
        $col1 = 130;
        $col2 = 530;
        $col3 = 730;
        $col4 = 980;
        
        $this->addText($image, 'Keterangan', $col1, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Jumlah', $col2, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Satuan', $col3, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Total', $col4, $y, $normalSize, $black, $fontPath, true);
        
        // Garis di bawah header
        $y += 15;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Data Tabel
        $y += 60;
        $itemName = $booking->barang->nama_barang;
        $quantity = (string)$booking->quantity;
        $unitPrice = 'Rp. ' . number_format($booking->barang->harga_sewa, 0, ',', '.');
        $total = 'Rp. ' . number_format($booking->total_amount, 0, ',', '.');
        
        $this->addText($image, $itemName, $col1, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $quantity, $col2, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $unitPrice, $col3, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $total, $col4, $y, $normalSize, $black, $fontPath);
        
        // Pemisah Footer Tabel
        $y += 60;
        $this->drawLine($image, 530, $y, $imageWidth - 130, $y, $black);
        
        // Total Pemesanan
        $y += 60;
        $this->addText($image, 'Total Pemesanan', 530, $y, $normalSize, $black, $fontPath);
        $this->addText($image, 'Rp. ' . number_format($booking->total_amount, 0, ',', '.'), 980, $y, $normalSize, $black, $fontPath);
        
        // Total Dibayar
        $y += $lineHeight;
        $this->addText($image, 'Total Dibayar', 530, $y, $headerSize, $black, $fontPath, true);
        $this->addText($image, 'Rp. ' . number_format($booking->total_amount, 0, ',', '.'), 980, $y, $headerSize, $black, $fontPath, true);
        
        // Tanda tangan footer
        // Jarak footer sebelum blok tanda tangan ditambahkan
        $y += 150;
        $location = 'Bengkalis';
        $date = $booking->created_at->locale('id')->isoFormat('DD MMMM YYYY');
        $this->addText($image, $location . ', ' . $date, 130, $y, $normalSize, $black, $fontPath, true);
        $y += $lineHeight;
        $this->addText($image, 'Hormat Kami', 130, $y, $normalSize, $black, $fontPath);
        
        // Tambahkan QR Code Validasi & Branding SiladesBeng
        $token = hash_hmac('sha256', $booking->id . $booking->order_number, config('app.key'));
        $qrUrl = url("/validasi/transaksi/rental/{$booking->id}?token={$token}");
        $this->addFooterTtd($image, $y, $qrUrl, $fontPath, $normalSize, $black);
        
        $y += 420; // Ruang untuk QR dan teks
         
        // Simpan bukti transaksi
        $filename = 'receipt_rental_' . $booking->order_number . '_' . time() . '.png';
        $path = 'receipts/rental/' . $filename;
        
        $fullPath = storage_path('app/public/' . dirname($path));
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        Storage::disk('public')->put($path, $imageData);
        
        imagedestroy($image);
        
        return $path;
    }

    /**
     * Buat bukti transaksi untuk pesanan gas
     */
    public function generateGasReceipt(GasOrder $order)
    {
        // Muat template latar belakang
        $backgroundPath = public_path('Admin/img/buktitransaksi/buktibeligasqr.png');
        
        if (!file_exists($backgroundPath)) {
            throw new \Exception('Background template not found: ' . $backgroundPath);
        }

        $temp = imagecreatefrompng($backgroundPath);
        $image = imagecreatetruecolor(imagesx($temp), imagesy($temp));
        imagecopy($image, $temp, 0, 0, 0, 0, imagesx($temp), imagesy($temp));
        imagedestroy($temp);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        
        // Atur warna
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 170, 0);
        
        // Jalur font
        $fontPath = public_path('fonts/arial.ttf');
        
        // Ukuran font
        $normalSize = 24;
        $headerSize = 28;
        
        // Tata letak disesuaikan
        // Tata letak disesuaikan
        $startY = 400;
        $lineHeight = 55;
        $labelX = 130;
        $valueX = 500;
        
        // No. Pesanan
        $y = $startY;
        $this->addText($image, 'No. Pesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $order->order_number, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Waktu Pemesanan
        $y += $lineHeight;
        $this->addText($image, 'Waktu Pemesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $order->created_at->locale('id')->isoFormat('dddd, DD MMMM YYYY  HH:mm') . ' WIB', $valueX, $y, $normalSize, $black, $fontPath);
        
        // Nama Pemesan
        $y += $lineHeight;
        $this->addText($image, 'Nama Akun Pemesan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $order->user->name, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Email
        $y += $lineHeight;
        $this->addText($image, 'Email Akun Pemesan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $order->user->email, $valueX, $y, $normalSize, $black, $fontPath);

        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Nama dan Alamat Pembeli Gas
        $y += 70;
        $this->addText($image, 'Nama dan Alamat Pembeli Gas', $labelX, $y, $headerSize, $black, $fontPath, true);

        // Nama Lengkap
        $y += 85;
        $this->addText($image, 'Nama Lengkap', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($order->full_name ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);

        // Alamat
        $y += $lineHeight;
        $this->addText($image, 'Alamat', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($order->address ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);
        
        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Informasi Pembayaran
        $y += 70;
        $this->addText($image, 'Informasi Pembayaran', $labelX, $y, $headerSize, $black, $fontPath, true);
        
        // Waktu Pembayaran
        $y += 85;
        $this->addText($image, 'Waktu Pembayaran', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($order->confirmed_at ? $order->confirmed_at->locale('id')->isoFormat('dddd, DD MMMM YYYY  HH:mm') . ' WIB' : '-'), $valueX, $y, $normalSize, $black, $fontPath);
        
        // Metode Pembayaran
        $y += $lineHeight;
        $this->addText($image, 'Metode Pembayaran', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $this->getPaymentMethodLabel($order->payment_method), $valueX, $y, $normalSize, $black, $fontPath);
        
        // Total Pembayaran
        $totalPrice = $order->price * $order->quantity;
        $y += $lineHeight;
        $this->addText($image, 'Total Pembayaran', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': Rp. ' . number_format($totalPrice, 0, ',', '.'), $valueX, $y, $normalSize, $black, $fontPath);
        
        // Status
        $y += $lineHeight;
        $this->addText($image, 'Status', $labelX, $y, $normalSize, $black, $fontPath, true);
        $statusText = $this->determineStatusLabel($order);
        
        // Tentukan warna status
        $statusColor = $black;
        if (in_array($order->status, ['completed', 'approved', 'confirmed', 'paid', 'arrived'])) {
            $statusColor = $green;
        } elseif (in_array($order->status, ['cancelled', 'rejected']) || ($order->cancellation_status ?? '') === 'pending') {
            $statusColor = $red;
        }

        $this->addText($image, ': ' . $statusText, $valueX, $y, $normalSize, $statusColor, $fontPath);
        
        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Detail Pembayaran
        $y += 70;
        $this->addText($image, 'Detail Pembayaran', $labelX, $y, $headerSize, $black, $fontPath, true);
        
        // Header Tabel
        $y += 85;
        $col1 = 130;
        $col2 = 530;
        $col3 = 730;
        $col4 = 980;
        
        $this->addText($image, 'Keterangan', $col1, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Jumlah', $col2, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Satuan', $col3, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Total', $col4, $y, $normalSize, $black, $fontPath, true);
        
        // Garis di bawah header
        $y += 15;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Data Tabel
        $y += 60;
        $itemName = $order->item_name;
        $quantity = (string)$order->quantity;
        $unitPrice = 'Rp. ' . number_format($order->price, 0, ',', '.');
        $total = 'Rp. ' . number_format($totalPrice, 0, ',', '.');
        
        $this->addText($image, $itemName, $col1, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $quantity, $col2, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $unitPrice, $col3, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $total, $col4, $y, $normalSize, $black, $fontPath);
        
        // Pemisah Footer Tabel
        $y += 60;
        $this->drawLine($image, 530, $y, $imageWidth - 130, $y, $black);
        
        // Total Pemesanan
        $y += 60;
        $this->addText($image, 'Total Pemesanan', 530, $y, $normalSize, $black, $fontPath);
        $this->addText($image, 'Rp. ' . number_format($totalPrice, 0, ',', '.'), 980, $y, $normalSize, $black, $fontPath);
        
        // Total Dibayar
        $y += $lineHeight;
        $this->addText($image, 'Total Dibayar', 530, $y, $headerSize, $black, $fontPath, true);
        $this->addText($image, 'Rp. ' . number_format($totalPrice, 0, ',', '.'), 980, $y, $headerSize, $black, $fontPath, true);
        
        // Tanda tangan footer
        // Jarak footer sebelum blok tanda tangan ditambahkan
        $y += 150;
        $location = 'Bengkalis';
        $date = $order->created_at->locale('id')->isoFormat('DD MMMM YYYY');
        $this->addText($image, $location . ', ' . $date, 130, $y, $normalSize, $black, $fontPath, true);
        $y += $lineHeight;
        $this->addText($image, 'Hormat Kami', 130, $y, $normalSize, $black, $fontPath);
        
        // Tambahkan QR Code Validasi & Branding SiladesBeng
        $token = hash_hmac('sha256', $order->id . $order->order_number, config('app.key'));
        $qrUrl = url("/validasi/transaksi/gas/{$order->id}?token={$token}");
        $this->addFooterTtd($image, $y, $qrUrl, $fontPath, $normalSize, $black);
        
        $y += 420; // Ruang untuk tanda tangan
        
        // Simpan bukti transaksi
        $filename = 'receipt_gas_' . $order->order_number . '_' . time() . '.png';
        $path = 'receipts/gas/' . $filename;
        
        $fullPath = storage_path('app/public/' . dirname($path));
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        Storage::disk('public')->put($path, $imageData);
        
        imagedestroy($image);
        
        return $path;
    }
    
    /**
     * Buat bukti transaksi untuk penyewaan mobil
     */
    public function generateMobilReceipt(\App\Models\MobilBooking $booking)
    {
        // Muat template latar belakang
        $backgroundPath = public_path('Admin/img/buktitransaksi/buktisewamobilqr.png');
        
        if (!file_exists($backgroundPath)) {
            throw new \Exception('Background template not found: ' . $backgroundPath);
        }

        $temp = imagecreatefrompng($backgroundPath);
        $image = imagecreatetruecolor(imagesx($temp), imagesy($temp));
        imagecopy($image, $temp, 0, 0, 0, 0, imagesx($temp), imagesy($temp));
        imagedestroy($temp);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 170, 0);
        
        $fontPath = public_path('fonts/arial.ttf');
        $normalSize = 24;
        $headerSize = 28;
        
        $startY = 400;
        $lineHeight = 55;
        $labelX = 130;
        $valueX = 500;
        
        // No. Pesanan
        $y = $startY;
        $this->addText($image, 'No. Pesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->order_number, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Waktu Pemesanan
        $y += $lineHeight;
        $this->addText($image, 'Waktu Pemesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->created_at->locale('id')->isoFormat('dddd, DD MMMM YYYY  HH:mm') . ' WIB', $valueX, $y, $normalSize, $black, $fontPath);
        
        // Nama Pemesan
        $y += $lineHeight;
        $this->addText($image, 'Nama Akun Pemesan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->user->name, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Email
        $y += $lineHeight;
        $this->addText($image, 'Email Akun Pemesan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->user->email, $valueX, $y, $normalSize, $black, $fontPath);

        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Informasi Sewa Mobil
        $y += 70;
        $this->addText($image, 'Informasi Sewa Mobil', $labelX, $y, $headerSize, $black, $fontPath, true);

        $y += 85;
        $this->addText($image, 'Nama Lengkap', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($booking->recipient_name ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);

        $y += $lineHeight;
        $this->addText($image, 'Opsi Pengambilan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ucfirst($booking->delivery_method ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);

        $y += $lineHeight;
        $this->addText($image, 'Opsi Supir', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($booking->dengan_supir ? 'Dengan Supir' : 'Supir Sendiri'), $valueX, $y, $normalSize, $black, $fontPath);
        
        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Informasi Pembayaran
        $y += 70;
        $this->addText($image, 'Informasi Pembayaran', $labelX, $y, $headerSize, $black, $fontPath, true);
        
        $y += 85;
        $this->addText($image, 'Waktu Pembayaran', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($booking->confirmed_at ? $booking->confirmed_at->locale('id')->isoFormat('dddd, DD MMMM YYYY  HH:mm') . ' WIB' : '-'), $valueX, $y, $normalSize, $black, $fontPath);
        
        $y += $lineHeight;
        $this->addText($image, 'Metode Pembayaran', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $this->getPaymentMethodLabel($booking->payment_method), $valueX, $y, $normalSize, $black, $fontPath);
        
        $y += $lineHeight;
        $this->addText($image, 'Total Pembayaran', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': Rp. ' . number_format($booking->total_harga ?? 0, 0, ',', '.'), $valueX, $y, $normalSize, $black, $fontPath);
        
        $y += $lineHeight;
        $this->addText($image, 'Status', $labelX, $y, $normalSize, $black, $fontPath, true);
        $statusText = $this->determineStatusLabel($booking);
        
        $statusColor = $black;
        if (in_array($booking->status, ['completed', 'approved', 'confirmed', 'paid', 'arrived', 'returned'])) {
            $statusColor = $green;
        } elseif (in_array($booking->status, ['cancelled', 'rejected']) || ($booking->cancellation_status ?? '') === 'pending') {
            $statusColor = $red;
        }

        $this->addText($image, ': ' . $statusText, $valueX, $y, $normalSize, $statusColor, $fontPath);
        
        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Detail Pembayaran
        $y += 70;
        $this->addText($image, 'Detail Pembayaran', $labelX, $y, $headerSize, $black, $fontPath, true);
        
        // Header Tabel
        $y += 85;
        $col1 = 130;
        $col2 = 530;
        $col3 = 730;
        $col4 = 980;
        
        $this->addText($image, 'Keterangan', $col1, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Durasi', $col2, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Harga/Hari', $col3, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Total', $col4, $y, $normalSize, $black, $fontPath, true);
        
        $y += 15;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        $y += 60;
        $itemName = $booking->mobil->nama_mobil ?? 'Sewa Mobil';
        $quantity = $booking->lama_sewa . ' Hari';
        $unitPrice = 'Rp. ' . number_format($booking->mobil->harga_sewa ?? 0, 0, ',', '.');
        $total = 'Rp. ' . number_format($booking->total_harga ?? 0, 0, ',', '.');
        
        $this->addText($image, substr($itemName, 0, 20), $col1, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $quantity, $col2, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $unitPrice, $col3, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $total, $col4, $y, $normalSize, $black, $fontPath);
        
        $y += 60;
        $this->drawLine($image, 530, $y, $imageWidth - 130, $y, $black);
        
        $y += 60;
        $this->addText($image, 'Total Pemesanan', 530, $y, $normalSize, $black, $fontPath);
        $this->addText($image, 'Rp. ' . number_format($booking->total_harga ?? 0, 0, ',', '.'), 980, $y, $normalSize, $black, $fontPath);
        
        $y += $lineHeight;
        $this->addText($image, 'Total Dibayar', 530, $y, $headerSize, $black, $fontPath, true);
        $this->addText($image, 'Rp. ' . number_format($booking->total_harga ?? 0, 0, ',', '.'), 980, $y, $headerSize, $black, $fontPath, true);
        
        $y += 150;
        $location = 'Bengkalis';
        $date = $booking->created_at->locale('id')->isoFormat('DD MMMM YYYY');
        $this->addText($image, $location . ', ' . $date, 130, $y, $normalSize, $black, $fontPath, true);
        $y += $lineHeight;
        $this->addText($image, 'Hormat Kami', 130, $y, $normalSize, $black, $fontPath);
        
        // Tambahkan QR Code Validasi & Branding SiladesBeng
        $token = hash_hmac('sha256', $booking->id . $booking->order_number, config('app.key'));
        $qrUrl = url("/validasi/transaksi/mobil/{$booking->id}?token={$token}");
        $this->addFooterTtd($image, $y, $qrUrl, $fontPath, $normalSize, $black);
        
        $y += 420;
        
        $filename = 'receipt_mobil_' . $booking->order_number . '_' . time() . '.png';
        $path = 'receipts/mobil/' . $filename;
        
        $fullPath = storage_path('app/public/' . dirname($path));
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        Storage::disk('public')->put($path, $imageData);
        
        imagedestroy($image);
        
        return $path;
    }

    /**
     * Buat bukti transaksi untuk peminjaman fasilitas umum
     */
    public function generateFasilitasReceipt(\App\Models\FasilitasUmumBooking $booking)
    {
        // Muat template latar belakang
        $backgroundPath = public_path('Admin/img/buktitransaksi/buktipinjamfasilitasumumqr.png');
        
        if (!file_exists($backgroundPath)) {
            throw new \Exception('Background template not found: ' . $backgroundPath);
        }

        $temp = imagecreatefrompng($backgroundPath);
        $image = imagecreatetruecolor(imagesx($temp), imagesy($temp));
        imagecopy($image, $temp, 0, 0, 0, 0, imagesx($temp), imagesy($temp));
        imagedestroy($temp);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 170, 0);
        
        $fontPath = public_path('fonts/arial.ttf');
        $normalSize = 24;
        $headerSize = 28;
        
        $startY = 400;
        $lineHeight = 55;
        $labelX = 130;
        $valueX = 500;
        
        // No. Pesanan
        $y = $startY;
        $this->addText($image, 'No. Pesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->order_number, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Waktu Pemesanan
        $y += $lineHeight;
        $this->addText($image, 'Waktu Pemesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->created_at->locale('id')->isoFormat('dddd, DD MMMM YYYY  HH:mm') . ' WIB', $valueX, $y, $normalSize, $black, $fontPath);
        
        // Nama Pemesan
        $y += $lineHeight;
        $this->addText($image, 'Nama Akun Pemesan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->user->name, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Email
        $y += $lineHeight;
        $this->addText($image, 'Email Akun Pemesan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $booking->user->email, $valueX, $y, $normalSize, $black, $fontPath);

        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Informasi Peminjaman
        $y += 70;
        $this->addText($image, 'Informasi Peminjaman', $labelX, $y, $headerSize, $black, $fontPath, true);

        $y += 85;
        $this->addText($image, 'Nama Lengkap', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($booking->recipient_name ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);

        $y += $lineHeight;
        $this->addText($image, 'Tujuan Peminjaman', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($booking->rental_purpose ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);

        $y += $lineHeight;
        $this->addText($image, 'Status', $labelX, $y, $normalSize, $black, $fontPath, true);
        $statusText = $this->determineStatusLabel($booking);
        
        $statusColor = $black;
        if (in_array($booking->status, ['completed', 'approved', 'confirmed', 'paid', 'arrived', 'returned'])) {
            $statusColor = $green;
        } elseif (in_array($booking->status, ['cancelled', 'rejected']) || ($booking->cancellation_status ?? '') === 'pending') {
            $statusColor = $red;
        }

        $this->addText($image, ': ' . $statusText, $valueX, $y, $normalSize, $statusColor, $fontPath);
        
        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Detail Peminjaman
        $y += 70;
        $this->addText($image, 'Detail Peminjaman', $labelX, $y, $headerSize, $black, $fontPath, true);
        
        // Header Tabel
        $y += 85;
        $col1 = 130;
        $col2 = 530;
        $col3 = 730;
        
        $this->addText($image, 'Fasilitas Umum', $col1, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Durasi', $col2, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Keterangan', $col3, $y, $normalSize, $black, $fontPath, true);
        
        $y += 15;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        $y += 60;
        $itemName = $booking->fasilitas->nama_fasilitas ?? 'Fasilitas Umum';
        $quantity = $booking->lama_sewa . ' Hari';
        $keterangan = 'Gratis';
        
        $this->addText($image, substr($itemName, 0, 20), $col1, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $quantity, $col2, $y, $normalSize, $black, $fontPath);
        $this->addText($image, $keterangan, $col3, $y, $normalSize, $black, $fontPath);
        
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        $y += 150;
        $location = 'Bengkalis';
        $date = $booking->created_at->locale('id')->isoFormat('DD MMMM YYYY');
        $this->addText($image, $location . ', ' . $date, 130, $y, $normalSize, $black, $fontPath, true);
        $y += $lineHeight;
        $this->addText($image, 'Hormat Kami', 130, $y, $normalSize, $black, $fontPath);
        
        // Tambahkan QR Code Validasi & Branding SiladesBeng
        $token = hash_hmac('sha256', $booking->id . $booking->order_number, config('app.key'));
        $qrUrl = url("/validasi/transaksi/fasilitas/{$booking->id}?token={$token}");
        $this->addFooterTtd($image, $y, $qrUrl, $fontPath, $normalSize, $black);
        
        $y += 420;
        
        $filename = 'receipt_fasilitas_' . $booking->order_number . '_' . time() . '.png';
        $path = 'receipts/fasilitas/' . $filename;
        
        $fullPath = storage_path('app/public/' . dirname($path));
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        Storage::disk('public')->put($path, $imageData);
        
        imagedestroy($image);
        
        return $path;
    }
    
    /**
     * Tambahkan teks rata tengah
     */
    protected function addCenteredText($image, $text, $y, $size, $color, $fontPath, $imageWidth, $bold = false) 
    {
        if (file_exists($fontPath)) {
            $bbox = imagettfbbox($size, 0, $fontPath, $text);
            $textWidth = $bbox[2] - $bbox[0];
            $x = ($imageWidth - $textWidth) / 2;
            
            imagettftext($image, $size, 0, $x, $y, $color, $fontPath, $text);
            
            if ($bold) {
                imagettftext($image, $size, 0, $x + 1, $y, $color, $fontPath, $text);
            }
        } else {
            // Alternatif untuk teks rata tengah jika font hilang (perkiraan kasar)
            $fontWidth = imagefontwidth(5);
            $textWidth = strlen($text) * $fontWidth;
            $x = ($imageWidth - $textWidth) / 2;
            imagestring($image, 5, $x, $y, $text, $color);
        }
    }

    /**
     * Gambar garis horizontal
     */
    protected function drawLine($image, $x1, $y, $x2, $y2, $color)
    {
        imagesetthickness($image, 2);
        imageline($image, $x1, $y, $x2, $y2, $color);
        imagesetthickness($image, 1);
    }

    /**
     * Tambahkan teks ke gambar menggunakan GD
     */
    protected function addText($image, $text, $x, $y, $size, $color, $fontPath, $bold = false)
    {
        if (file_exists($fontPath)) {
            imagettftext($image, $size, 0, $x, $y, $color, $fontPath, $text);
            
            if ($bold) {
                imagettftext($image, $size, 0, $x + 1, $y, $color, $fontPath, $text);
                imagettftext($image, $size, 0, $x, $y + 1, $color, $fontPath, $text);
                imagettftext($image, $size, 0, $x + 1, $y + 1, $color, $fontPath, $text);
            }
        } else {
            imagestring($image, 5, $x, $y, $text, $color);
        }
    }

    protected function getPaymentMethodLabel($method)
    {
        $labels = [
            'transfer' => 'Transfer Manual',
            'tunai' => 'Tunai (Cash)',
            'cash' => 'Tunai (Cash)',
            'qris' => 'QRIS (Digital Payment)',
            'gopay' => 'GoPay (Digital Payment)',
            'shopeepay' => 'ShopeePay (Digital Payment)',
            'bank_transfer' => 'Virtual Account / Bank Transfer',
            'midtrans' => 'Payment Gateway (Digital)',
            'credit_card' => 'Kartu Kredit',
        ];
        
        $methodLower = strtolower(trim($method));
        return $labels[$methodLower] ?? ucwords(str_replace('_', ' ', $methodLower)) . ' (Digital Payment)';
    }

    /**
     * Tentukan label status berdasarkan kondisi real-time
     */
    protected function determineStatusLabel($model)
    {
        // 1. Cek Permintaan Pembatalan terlebih dahulu
        if (isset($model->cancellation_status) && $model->cancellation_status === 'pending') {
            return 'Permintaan Pembatalan';
        }

        // 2. Mapping Status Utama
        $labels = [
            'pending' => 'Di Proses', // Atau 'Menunggu Konfirmasi'
            'confirmed' => 'Dikonfirmasi', // REVISI: Bukan 'Lunas / Selesai'
            'approved' => 'Disetujui',
            'in_progress' => 'Dalam Proses',
            'being_prepared' => 'Sedang Dipersiapkan',
            'in_delivery' => 'Dalam Pengiriman',
            'arrived' => 'Tiba di Lokasi',
            'completed' => 'Selesai',
            'returned' => 'Dikembalikan',
            'cancelled' => 'Dibatalkan',
            'rejected' => 'Ditolak',
            'paid' => 'Sudah Bayar',
        ];

        return $labels[$model->status] ?? ucfirst($model->status);
    }

    /**
     * Helper untuk menambahkan QR Code & Teks Branding di Footer
     */

    /**
     * Buat bukti transaksi untuk pesanan pasar daerah
     */
    public function generatePasarReceipt(\App\Models\PasarOrder $order)
    {
        // Muat template latar belakang
        $backgroundPath = public_path('User/img/buktipasardaerah/bukti transaksi pasar daerah.png');
        
        if (!file_exists($backgroundPath)) {
            throw new \Exception('Background template not found: ' . $backgroundPath);
        }

        $temp = imagecreatefrompng($backgroundPath);
        $image = imagecreatetruecolor(imagesx($temp), imagesy($temp));
        imagecopy($image, $temp, 0, 0, 0, 0, imagesx($temp), imagesy($temp));
        imagedestroy($temp);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        
        // Atur warna
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 170, 0);
        
        // Jalur font
        $fontPath = public_path('fonts/arial.ttf');
        
        // Ukuran font
        $normalSize = 24;
        $headerSize = 28;
        
        // Tata letak disesuaikan
        $startY = 400;
        $lineHeight = 55;
        $labelX = 130;
        $valueX = 500;
        
        // No. Pesanan
        $y = $startY;
        $this->addText($image, 'No. Pesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $order->order_number, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Waktu Pemesanan
        $y += $lineHeight;
        $this->addText($image, 'Waktu Pemesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $order->created_at->locale('id')->isoFormat('dddd, DD MMMM YYYY  HH:mm') . ' WIB', $valueX, $y, $normalSize, $black, $fontPath);
        
        // Nama Pemesan
        $y += $lineHeight;
        $this->addText($image, 'Nama Akun Pemesan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . $order->user->name, $valueX, $y, $normalSize, $black, $fontPath);
        
        // NIK (Sensor)
        $y += $lineHeight;
        $this->addText($image, 'NIK', $labelX, $y, $normalSize, $black, $fontPath, true);
        $nik = $order->user->nik ?? '';
        $censoredNik = '-';
        if (strlen($nik) >= 8) {
            $censoredNik = substr($nik, 0, 4) . str_repeat('*', strlen($nik) - 8) . substr($nik, -4);
        } elseif (strlen($nik) > 0) {
            $censoredNik = '****';
        }
        $this->addText($image, ': ' . $censoredNik, $valueX, $y, $normalSize, $black, $fontPath);

        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Informasi Pengiriman
        $y += 70;
        $this->addText($image, 'Informasi Pengiriman', $labelX, $y, $headerSize, $black, $fontPath, true);

        // Nama Penerima
        $y += 85;
        $this->addText($image, 'Nama Penerima', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($order->recipient_name ?? $order->user->name), $valueX, $y, $normalSize, $black, $fontPath);

        // No HP
        $y += $lineHeight;
        $this->addText($image, 'No. Handphone', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($order->recipient_phone ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);

        // Alamat Pengiriman
        $y += $lineHeight;
        $this->addText($image, 'Alamat', $labelX, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, ': ' . ($order->shipping_address ?? '-'), $valueX, $y, $normalSize, $black, $fontPath);
        
        // Metode Pengiriman
        $y += $lineHeight;
        $this->addText($image, 'Metode Pengiriman', $labelX, $y, $normalSize, $black, $fontPath, true);
        $shippingMethod = ($order->shipping_method == 'delivery') ? 'Diantar (Kurir)' : (($order->shipping_method == 'pickup') ? 'Ambil Sendiri' : '-');
        $this->addText($image, ': ' . $shippingMethod, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Informasi Pembayaran
        $y += 70;
        $this->addText($image, 'Informasi Pembayaran', $labelX, $y, $headerSize, $black, $fontPath, true);
        
        // Metode Pembayaran
        $y += 85;
        $this->addText($image, 'Metode Pembayaran', $labelX, $y, $normalSize, $black, $fontPath, true);
        // Karena ada perbedaan method payment, bisa fallback manual jika undefined
        $paymentLabel = method_exists($this, 'getPaymentMethodLabel') ? $this->getPaymentMethodLabel($order->payment_method) : $order->payment_method;
        $this->addText($image, ': ' . $paymentLabel, $valueX, $y, $normalSize, $black, $fontPath);
        
        // Status Pembayaran/Pesanan
        $y += $lineHeight;
        $this->addText($image, 'Status Pesanan', $labelX, $y, $normalSize, $black, $fontPath, true);
        $statusText = strtoupper($order->status);
        
        // Tentukan warna status
        $statusColor = $black;
        if (in_array($order->status, ['completed', 'approved', 'confirmed', 'paid', 'arrived'])) {
            $statusColor = $green;
        } elseif (in_array($order->status, ['cancelled', 'rejected'])) {
            $statusColor = $red;
        }

        $this->addText($image, ': ' . $statusText, $valueX, $y, $normalSize, $statusColor, $fontPath, true);
        
        // Pemisah
        $y += 60;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Header: Detail Pesanan
        $y += 70;
        $this->addText($image, 'Detail Pesanan', $labelX, $y, $headerSize, $black, $fontPath, true);
        
        // Header Tabel
        $y += 85;
        $col1 = 130;
        $col2 = 630;
        $col3 = 780;
        $col4 = 980;
        
        $this->addText($image, 'Nama Produk', $col1, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Jumlah', $col2, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Harga Satuan', $col3, $y, $normalSize, $black, $fontPath, true);
        $this->addText($image, 'Total', $col4, $y, $normalSize, $black, $fontPath, true);
        
        // Garis di bawah header tabel
        $y += 15;
        $this->drawLine($image, 130, $y, $imageWidth - 130, $y, $black);
        
        // Data Tabel (Loop item pasar)
        $y += 60;
        foreach($order->items as $item) {
            $productName = $item->produk->nama_produk ?? $item->product_name;
            // Potong nama produk jika terlalu panjang
            if(strlen($productName) > 30) {
                $productName = substr($productName, 0, 27) . '...';
            }
            $quantity = (string)$item->quantity;
            $unitPrice = 'Rp. ' . number_format($item->price, 0, ',', '.');
            $subtotal = 'Rp. ' . number_format($item->subtotal, 0, ',', '.');
            
            $this->addText($image, $productName, $col1, $y, $normalSize, $black, $fontPath);
            $this->addText($image, $quantity, $col2, $y, $normalSize, $black, $fontPath);
            $this->addText($image, $unitPrice, $col3, $y, $normalSize, $black, $fontPath);
            $this->addText($image, $subtotal, $col4, $y, $normalSize, $black, $fontPath);
            
            $y += 50;
        }
        
        // Pemisah Footer Tabel
        $y += 10;
        $this->drawLine($image, 530, $y, $imageWidth - 130, $y, $black);
        
        // Subtotal
        $y += 60;
        $this->addText($image, 'Total Harga Produk', 530, $y, $normalSize, $black, $fontPath);
        $this->addText($image, 'Rp. ' . number_format($order->total_price, 0, ',', '.'), 980, $y, $normalSize, $black, $fontPath);
        
        // Ongkos Kirim
        $y += $lineHeight;
        $this->addText($image, 'Ongkos Kirim', 530, $y, $normalSize, $black, $fontPath);
        $this->addText($image, 'Rp. ' . number_format($order->shipping_cost, 0, ',', '.'), 980, $y, $normalSize, $black, $fontPath);
        
        // Grand Total
        $y += $lineHeight;
        $this->addText($image, 'Total Pembayaran', 530, $y, $headerSize, $black, $fontPath, true);
        $this->addText($image, 'Rp. ' . number_format($order->grand_total, 0, ',', '.'), 980, $y, $headerSize, $black, $fontPath, true);
        
        // Tanda tangan footer
        $y += 150;
        $location = 'Bengkalis';
        $date = $order->created_at->locale('id')->isoFormat('DD MMMM YYYY');
        $this->addText($image, $location . ', ' . $date, 130, $y, $normalSize, $black, $fontPath, true);
        $y += $lineHeight;
        $this->addText($image, 'Hormat Kami', 130, $y, $normalSize, $black, $fontPath);
        
        // Tambahkan QR Code Validasi & Branding SiladesBeng
        $token = hash_hmac('sha256', $order->id . $order->order_number, config('app.key'));
        $qrUrl = url("/validasi/transaksi/pasar-daerah/{$order->id}?token={$token}");
        $this->addFooterTtd($image, $y, $qrUrl, $fontPath, $normalSize, $black);
        
        $filename = 'receipt_pasar_' . $order->order_number . '_' . time() . '.png';
        $path = 'receipts/pasar/' . $filename;
        
        $fullPath = storage_path('app/public/' . dirname($path));
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);
        imagedestroy($image);
        
        return $path;
    }


    protected function addFooterTtd($image, $y, $url, $fontPath, $normalSize, $black)
    {
        $imageWidth = imagesx($image);
        $qrSize = 250; // Perbesar QR Code sedikit
        $yQr = $y + 60; // Jarak QR dari tulisan Hormat Kami
        
        // Posisikan QR di tengah (Center)
        $qrX = ($imageWidth - $qrSize) / 2;
        
        // 1. Generate & Tempel QR Code via API
        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$qrSize}x{$qrSize}&data=" . urlencode($url);
        
        try {
            // Kita bypass SSL error jika server lokal
            $context = stream_context_create([
                "ssl" => [
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ]
            ]);
            $qrData = @file_get_contents($qrApiUrl, false, $context);
            if ($qrData) {
                $qrImage = @imagecreatefromstring($qrData);
                if ($qrImage) {
                    // Copy QR Code ke main image
                    imagecopyresampled($image, $qrImage, $qrX, $yQr, 0, 0, $qrSize, $qrSize, imagesx($qrImage), imagesy($qrImage));
                    
                    // Tambahkan Logo SiladesBeng di tengah QR Code
                    $logoPath = public_path('Admin/img/illustrations/logodomain.png');
                    if (file_exists($logoPath)) {
                        $logoImage = @imagecreatefrompng($logoPath);
                        if ($logoImage) {
                            $logoSize = $qrSize * 0.35; // Perbesar porsi logo menjadi 35% agar lebih jelas
                            $logoX = $qrX + ($qrSize - $logoSize) / 2;
                            $logoY = $yQr + ($qrSize - $logoSize) / 2;
                            
                            // Buat background putih untuk logo (padding 6px agar kotak putih proporsional)
                            imagefilledrectangle($image, $logoX - 6, $logoY - 6, $logoX + $logoSize + 6, $logoY + $logoSize + 6, imagecolorallocate($image, 255, 255, 255));
                            
                            // Tempel logo di atas background putih tersebut
                            imagecopyresampled($image, $logoImage, $logoX, $logoY, 0, 0, $logoSize, $logoSize, imagesx($logoImage), imagesy($logoImage));
                            imagedestroy($logoImage);
                        }
                    }
                    
                    imagedestroy($qrImage);
                }
            }
        } catch (\Exception $e) {
            // Abaikan jika API gagal, lanjut render teks saja
        }
        
        // 2. Tambahkan Branding SiladesBeng di bawah QR (Rata Tengah)
        $yBranding = $yQr + $qrSize + 40;
        
        // Menghitung bounding box teks agar bisa rata tengah
        $bboxTitle = imagettfbbox($normalSize + 4, 0, $fontPath, 'SiladesBeng');
        $titleWidth = $bboxTitle[2] - $bboxTitle[0];
        $titleX = ($imageWidth - $titleWidth) / 2;
        
        $bboxDesc = imagettfbbox($normalSize - 4, 0, $fontPath, 'Platform E-Government Kab. Bengkalis');
        $descWidth = $bboxDesc[2] - $bboxDesc[0];
        $descX = ($imageWidth - $descWidth) / 2;
        
        $this->addText($image, 'SiladesBeng', $titleX, $yBranding, $normalSize + 4, $black, $fontPath, true); // Bold
        $this->addText($image, 'Platform E-Government Kab. Bengkalis', $descX, $yBranding + 35, $normalSize - 4, $black, $fontPath);
    }
}



