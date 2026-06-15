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
        $backgroundPath = public_path('Admin/img/transaksi/bukti-penyewaan-alat.png');
        
        if (!file_exists($backgroundPath)) {
            throw new \Exception('Background template not found: ' . $backgroundPath);
        }

        $image = imagecreatefrompng($backgroundPath);
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
        
        $y += 420; // Ruang untuk tanda tangan (Lebih besar karena teks SiladesBeng dihapus sesuai edit pengguna, tapi butuh ruang)
         
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
        $backgroundPath = public_path('Admin/img/transaksi/bukti-gas.png');
        
        if (!file_exists($backgroundPath)) {
            throw new \Exception('Background template not found: ' . $backgroundPath);
        }

        $image = imagecreatefrompng($backgroundPath);
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
        
        $y += 420; // Ruang untuk tanda tangan (Lebih besar karena teks SiladesBeng dihapus sesuai edit pengguna, tapi butuh ruang)
        
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
            'transfer' => 'Transfer - Bank Syariah Indonesia',
            'tunai' => 'Pembayaran Tunai',
            'cash' => 'Pembayaran Tunai',
        ];
        return $labels[$method] ?? ucfirst($method);
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
}
