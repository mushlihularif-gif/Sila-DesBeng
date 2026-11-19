<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpCenterController extends Controller
{
    public function index()
    {
        $faqs = [
            ['category'=>'Layanan dan pesanan', 'items'=>[
                ['question'=>'Bagaimana memilih alamat pengantaran?', 'answer'=>'Pilih alamat tersimpan pada halaman pemesanan, atau gunakan alamat baru dan tentukan titiknya di peta. Periksa alamat sebelum mengonfirmasi pesanan.'],
                ['question'=>'Di mana melihat perkembangan pesanan?', 'answer'=>'Buka Aktivitas, lalu pilih jenis layanan. Untuk koordinasi jadwal atau pengantaran, gunakan Chat Pengelola pada layanan terkait.'],
                ['question'=>'Apakah transfer membutuhkan bukti?', 'answer'=>'Ya. Unggah bukti transfer pada pesanan. Pengelola memeriksa pembayaran sebelum melanjutkan pesanan.'],
            ]],
            ['category'=>'Akun dan kabar daerah', 'items'=>[
                ['question'=>'Bagaimana memperbarui profil dan alamat?', 'answer'=>'Buka Profil untuk informasi profil. Alamat, domisili, dan pilihan pemberitahuan tersedia di Pengaturan.'],
                ['question'=>'Bagaimana mengirim berita daerah?', 'answer'=>'Pada Kabar dan Informasi Daerah, pilih Berita Daerah lalu tulis kiriman. Kirim ke Admin untuk ditinjau; menyimpan draf saja belum menerbitkannya.'],
                ['question'=>'Bagaimana mengatur pemberitahuan?', 'answer'=>'Buka Pengaturan, lalu bagian Notifikasi. Pilih pemberitahuan yang dibutuhkan dan simpan.'],
            ]],
        ];
        return view('help-center.index', compact('faqs'));
    }

    // Old ticket URLs had no views, database migration, or administrative inbox.
    // Keep their URLs usable without pretending a support request was recorded.
    private function contactHelp()
    {
        return redirect()->to(route('help-center.index').'#hubungi')
            ->with('info', 'Tiket bantuan belum tersedia. Gunakan kontak bantuan di bawah atau Chat Pengelola untuk pesanan.');
    }

    public function myTickets() { return $this->contactHelp(); }
    public function create() { return $this->contactHelp(); }
    public function store(Request $request) { return $this->contactHelp(); }
    public function show($id) { return $this->contactHelp(); }
    public function close($id) { return $this->contactHelp(); }
}
