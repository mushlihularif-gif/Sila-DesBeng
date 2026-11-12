<?php

namespace App\Services;

use App\Models\{AdminNotification, Barang, Region, RentalBooking, TransactionReceipt, User, WalletTransaction};
use App\Support\ProfilPembayaranWilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RentalOrdering
{
    public function visibleQuery(?User $user)
    {
        $line = Region::garisLayananUntukWarga($user?->region_id);
        return Barang::query()->where('status', '!=', 'rusak')->whereHas('region', function ($q) use ($line) {
            $q->where(function ($q) use ($line) {
                $q->whereHas('services', fn ($s) => $s->where('slug', 'penyewaan-alat')->where('region_services.is_active', true)
                    ->where(fn ($p) => $p->where('region_services.is_exclusive', false)->orWhereIn('regions.id', $line)))
                    ->orWhere(fn ($own) => $own->whereIn('id', $line)->whereDoesntHave('services', fn ($s) => $s->where('slug', 'penyewaan-alat')));
            });
        });
    }

    /** A package is committed together with all receipts, ledger rows and notices. */
    public function store(Request $request, bool $package = false): array
    {
        abort_unless($request->user(), 401);
        $data = $request->validate([
            'barang_id' => $package ? 'nullable|integer' : 'required|integer|exists:barang,id',
            'quantity' => $package ? 'nullable|integer' : 'required|integer|min:1|max:50',
            'items' => $package ? 'required|array|min:1|max:20' : 'prohibited',
            'items.*.barang_id' => 'required|integer|distinct|exists:barang,id',
            'items.*.quantity' => 'required|integer|min:1|max:50',
            'start_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'delivery_method' => 'required|in:antar,jemput',
            'recipient_name' => 'required|string|max:255',
            'delivery_address' => 'required|string|max:1000',
            'rental_purpose' => 'required|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90', 'longitude' => 'nullable|numeric|between:-180,180',
            'payment_method' => 'required|in:tunai,transfer,ewallet',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'sop_accepted' => 'required|accepted', 'idempotency_key' => 'nullable|uuid',
        ]);
        $days = (int) \Carbon\Carbon::parse($data['start_date'])->diffInDays(\Carbon\Carbon::parse($data['end_date'])) + 1;
        if ($days > 366) throw ValidationException::withMessages(['end_date' => 'Masa sewa maksimal 366 hari.']);
        $items = $package ? $data['items'] : [['barang_id' => $data['barang_id'], 'quantity' => $data['quantity']]];
        usort($items, fn ($a, $b) => $a['barang_id'] <=> $b['barang_id']);
        $paths = [];
        try {
            return DB::transaction(function () use ($request, $data, $items, $days, $package, &$paths) {
                $user = User::lockForUpdate()->findOrFail($request->user()->id);
                abort_unless($user->verification_status === 'verified', 403, 'Verifikasi identitas terlebih dahulu sebelum menyewa alat.');
                $payload = $data;
                unset($payload['payment_proof'], $payload['idempotency_key'], $payload['barang_id'], $payload['quantity'], $payload['items']);
                $hash = hash('sha256', json_encode([$payload, $items]));
                $orders = []; $provider = null;
                foreach ($items as $index => $item) {
                    $key = empty($data['idempotency_key']) ? null : ($package ? (string) \Ramsey\Uuid\Uuid::uuid5($data['idempotency_key'], (string) $item['barang_id']) : $data['idempotency_key']);
                    if ($key && $existing = RentalBooking::withTrashed()->where('user_id', $user->id)->where('idempotency_key', $key)->first()) {
                        abort_unless(hash_equals($existing->submission_hash ?? '', $hash), 409, 'Isi pesanan berubah. Buka ulang formulir untuk pesanan baru.');
                        $orders[] = $existing;
                        continue;
                    }
                    $barang = Barang::with('region')->lockForUpdate()->findOrFail($item['barang_id']);
                    abort_unless($this->visibleQuery($user)->whereKey($barang->id)->exists(), 403, 'Layanan ini tidak tersedia untuk wilayah akun Anda.');
                    abort_unless($provider === null || $provider === $barang->region_id, 422, 'Satu paket hanya boleh berisi alat dari satu wilayah penyedia.');
                    $provider = $barang->region_id;
                    if ($barang->status !== 'tersedia' || !$barang->hasStock($item['quantity'])) throw ValidationException::withMessages(['quantity' => 'Alat tidak tersedia atau stok tidak mencukupi.']);
                    $profile = ProfilPembayaranWilayah::untuk($provider);
                    if (!in_array($data['payment_method'], $profile->metodeTersedia(), true)) throw ValidationException::withMessages(['payment_method' => 'Metode pembayaran tidak tersedia di wilayah penyedia.']);
                    if (!($barang->region->payment_info['alat_delivery_'.$data['delivery_method'].'_active'] ?? true)) throw ValidationException::withMessages(['delivery_method' => 'Metode pengambilan/pengiriman dinonaktifkan pengelola.']);
                    $total = round((float) $barang->harga_sewa * $item['quantity'], 2);
                    abort_unless($total > 0 && $total <= 9999999999.99, 422, 'Nilai pesanan di luar batas. Hubungi pengelola.');
                    // Separate file per booking so replacing one proof cannot delete another booking's proof.
                    $path = null;
                    if ($data['payment_method'] !== 'tunai' && $request->hasFile('payment_proof')) {
                        $path = $request->file('payment_proof')->store('payment_proofs/rental', 'public');
                        abort_unless($path, 503, 'Bukti gagal disimpan.'); $paths[] = $path;
                    }
                    $order = new RentalBooking(array_intersect_key($data, array_flip(['start_date','end_date','delivery_method','recipient_name','delivery_address','rental_purpose','latitude','longitude','payment_method'])) + [
                        'user_id' => $user->id, 'barang_id' => $barang->id, 'region_id' => $provider,
                        'order_number' => 'RNT-'.Str::ulid(), 'quantity' => $item['quantity'], 'days_count' => $days,
                        'total_amount' => $total, 'payment_proof' => $path, 'status' => 'pending',
                    ]);
                    $order->unit_price = $barang->harga_sewa; $order->idempotency_key = $key; $order->submission_hash = $hash; $order->save();
                    TransactionReceipt::create(['booking_type'=>'rental','booking_id'=>$order->id,'receipt_number'=>$order->order_number,
                        'user_id'=>$user->id,'region_id'=>$provider,'item_name'=>$barang->nama_barang,'quantity'=>$order->quantity,
                        'total_amount'=>$total,'payment_method'=>$data['payment_method']]);
                    WalletTransaction::catatPemasukan($provider, 'rental', $order->id, $total, $data['payment_method'], $path);
                    AdminNotification::create(['type'=>'rental_request','reference_id'=>$order->id,'region_id'=>$provider,
                        'title'=>'Pemesanan Alat Baru','message'=>'Pesanan '.$order->order_number.' menunggu pemeriksaan.','is_read'=>false]);
                    \App\Models\Notification::create(['user_id'=>$user->id,'type'=>'order_created','title'=>'Permintaan Sewa Alat Terkirim',
                        'message'=>'Pesanan '.$order->order_number.' menunggu pemeriksaan pengelola.','link'=>route('rental.payment',$order->id),'icon'=>'bx bx-wrench','is_read'=>false,'sent_at'=>now()]);
                    $orders[] = $order;
                }
                return $orders;
            });
        } catch (\Throwable $e) {
            foreach ($paths as $path) Storage::disk('public')->delete($path);
            throw $e;
        }
    }
}
