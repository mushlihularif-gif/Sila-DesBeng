<?php

namespace App\Http\Controllers;

use App\Models\HelpTicket;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HelpCenterController extends Controller
{
    /**
     * Halaman utama help center
     */
    public function index()
    {
        $faqs = $this->getFAQs();
        $myTicketsCount = HelpTicket::where('user_id', auth()->id())->count();
        
        return view('help-center.index', compact('faqs', 'myTicketsCount'));
    }

    /**
     * Halaman daftar tiket user
     */
    public function myTickets()
    {
        $tickets = HelpTicket::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('help-center.my-tickets', compact('tickets'));
    }

    /**
     * Form buat tiket baru
     */
    public function create()
    {
        return view('help-center.create');
    }

    /**
     * Simpan tiket baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|in:bug,fitur,akun,laporan,lainnya',
            'description' => 'required|string|min:20',
            'priority' => 'required|in:rendah,normal,tinggi,urgent',
            'screenshot' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ], [
            'subject.required' => 'Subjek wajib diisi',
            'category.required' => 'Kategori wajib dipilih',
            'description.required' => 'Deskripsi wajib diisi',
            'description.min' => 'Deskripsi minimal 20 karakter',
            'screenshot.image' => 'File harus berupa gambar',
            'screenshot.max' => 'Ukuran gambar maksimal 2MB',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'baru';

        // Upload screenshot jika ada
        if ($request->hasFile('screenshot')) {
            $validated['screenshot'] = $request->file('screenshot')->store('help-tickets', 'public');
        }

        $ticket = HelpTicket::create($validated);

        // Kirim notifikasi ke admin
        $this->notifyAdmins($ticket);

        return redirect()->route('help-center.show', $ticket->id)
            ->with('success', '✅ Tiket berhasil dibuat! Admin akan segera merespons.');
    }

    /**
     * Detail tiket
     */
    public function show($id)
    {
        $ticket = HelpTicket::with(['user', 'handler'])->findOrFail($id);

        // Pastikan hanya pemilik yang bisa lihat
        if ($ticket->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return view('help-center.show', compact('ticket'));
    }

    /**
     * Tutup tiket (user)
     */
    public function close($id)
    {
        $ticket = HelpTicket::findOrFail($id);

        if ($ticket->user_id !== auth()->id()) {
            abort(403);
        }

        $ticket->update(['status' => 'ditutup']);

        return back()->with('success', '✅ Tiket berhasil ditutup!');
    }

    /**
     * Get FAQs
     */
    private function getFAQs()
    {
        return [
            [
                'category' => 'Umum',
                'icon' => '❓',
                'items' => [
                    [
                        'question' => 'Bagaimana cara membuat laporan?',
                        'answer' => 'Klik menu "Laporan Saya" → "Buat Laporan Baru" → Isi form dengan lengkap → Submit. Laporan Anda akan segera ditinjau oleh admin.'
                    ],
                    [
                        'question' => 'Berapa lama laporan diproses?',
                        'answer' => 'Biasanya admin akan merespons dalam 1-2 hari kerja. Anda akan mendapat notifikasi saat status laporan berubah.'
                    ],
                    [
                        'question' => 'Bagaimana cara memberikan rating?',
                        'answer' => 'Setelah laporan selesai, buka detail laporan → klik tombol "Berikan Rating" → Pilih bintang dan tulis feedback.'
                    ],
                ]
            ],
            [
                'category' => 'Akun',
                'icon' => '👤',
                'items' => [
                    [
                        'question' => 'Lupa password, bagaimana?',
                        'answer' => 'Klik "Lupa Password" di halaman login → Masukkan email → Cek inbox untuk link reset password.'
                    ],
                    [
                        'question' => 'Bagaimana cara login dengan Google?',
                        'answer' => 'Klik tombol "Login dengan Google" di halaman login → Pilih akun Google Anda → Lengkapi data RT/RW jika diperlukan.'
                    ],
                    [
                        'question' => 'Bagaimana cara mengubah profil?',
                        'answer' => 'Klik nama Anda di pojok kanan atas → "Profil" → Edit informasi → Simpan perubahan.'
                    ],
                ]
            ],
            [
                'category' => 'Notifikasi',
                'icon' => '🔔',
                'items' => [
                    [
                        'question' => 'Kenapa tidak mendapat notifikasi?',
                        'answer' => 'Pastikan browser Anda mengizinkan notifikasi. Cek juga apakah email Anda sudah terverifikasi.'
                    ],
                    [
                        'question' => 'Bagaimana cara menghapus notifikasi?',
                        'answer' => 'Buka halaman Notifikasi → Klik ikon tempat sampah pada notifikasi yang ingin dihapus.'
                    ],
                ]
            ],
            
        ];
    }

    /**
     * Notifikasi ke admin
     */
    private function notifyAdmins($ticket)
    {
        $admins = \App\Models\User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            NotificationService::send(
                $admin->id,
                'help_ticket_baru',
                '🆘 Tiket Bantuan Baru!',
                "{$ticket->user->name} membutuhkan bantuan: {$ticket->subject}",
                '/admin/help-tickets/' . $ticket->id,
                '🆘'
            );
        }
    }
}