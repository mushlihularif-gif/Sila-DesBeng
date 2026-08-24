<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GmailInboxService;
use Illuminate\Http\Request;

/**
 * Endpoint JSON untuk panel kotak masuk di sisi kanan dashboard.
 *
 * Sengaja dipisah dari DashboardController dan dimuat lewat AJAX setelah
 * halaman tampil: koneksi IMAP bisa memakan beberapa detik, dan itu tidak
 * boleh menahan tampilnya dashboard.
 */
class InboxController extends Controller
{
    public function __construct(private GmailInboxService $inbox)
    {
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->inbox->latest(15, $request->boolean('segarkan'))
        );
    }

    public function show(int $uid)
    {
        $hasil = $this->inbox->message($uid);

        $kode = match ($hasil['status']) {
            'ok'           => 200,
            'tidak_ada'    => 404,
            'belum_diatur' => 409,
            default        => 502,
        };

        return response()->json($hasil, $kode);
    }
}
