<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\ContentAccess;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request) { return redirect()->route('content.index'); }
    public function create(Request $request) { return redirect()->route('content.index'); }
    public function show($id) { return $this->edit(request(), $id); }
    public function edit(Request $request, $id)
    {
        $item = Announcement::findOrFail($id);
        abort_unless(app(ContentAccess::class)->canInspect($request->user(), $item), 403);
        return redirect()->route('content.show', $item);
    }
    public function store(Request $request) { abort(409, 'Gunakan editor Kabar dan Informasi Daerah yang baru untuk membuat publikasi.'); }
    public function update(Request $request, $id) { abort(409, 'Muat editor terbaru dan mulai penanganan sebelum menyimpan.'); }
    public function destroy($id) { abort(409, 'Gunakan editor terbaru untuk menangani atau membatalkan publikasi.'); }
}
