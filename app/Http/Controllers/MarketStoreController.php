<?php
namespace App\Http\Controllers;

use App\Models\{MarketStore, PasarProduk, PasarOrder, Region, User, Notification};
use App\Services\{MarketShippingService, MarketStoreOrders};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\Rule;

class MarketStoreController extends Controller
{
    private function owner(Request $r): User
    {
        abort_unless($r->user()?->role === 'user',403,'Toko warga dikelola melalui akun pengguna biasa.');
        return $r->user();
    }
    private function owned(Request $r): MarketStore { return MarketStore::where('user_id',$this->owner($r)->id)->firstOrFail(); }
    private function approved(Request $r): MarketStore
    {
        $store=$this->owned($r);
        abort_unless($store->status === 'approved' && $r->user()->verification_status === 'verified',403,'Toko harus disetujui dan akun harus terverifikasi.');
        return $store;
    }
    private function reply(Request $r, string $message, string $route='market.seller.home')
    {
        return $r->expectsJson() ? response()->json(['message'=>$message]) : redirect()->route($route)->with('success',$message);
    }
    public function home(Request $r)
    {
        $owner=$this->owner($r); $store=MarketStore::where('user_id',$owner->id)->first();
        $regions=Region::whereIn('type',['desa','kelurahan'])->whereHas('parent',fn($q)=>$q->where('name','Kecamatan Bengkalis')->where('type','kecamatan'))->orderBy('name')->get();
        $products=$store ? $store->products()->latest()->paginate(12,['*'],'products') : collect();
        $orders=$store ? $store->orders()->latest()->paginate(10,['*'],'orders') : collect();
        $shippingRegions=Region::whereIn('type',['desa','kelurahan','kecamatan'])->with('parent:id,name')->orderBy('name')->get();
        $shippingConfig=$store ? app(MarketShippingService::class)->configuration($store->shippingRegion()) : [];
        $profileVillage=app(MarketShippingService::class)->village($owner->region_id);
        $threads=$store ? DB::table('market_store_messages')->where('store_id',$store->id)->select('buyer_id')->distinct()->pluck('buyer_id') : collect();
        $contacts=User::whereIn('id',$threads)->get(['id','name']);
        if($r->expectsJson()) return response()->json(compact('store','products','orders','regions','shippingConfig','contacts'));
        return view('market.manage',compact('store','owner','regions','products','orders','shippingRegions','shippingConfig','profileVillage','contacts'));
    }
    public function saveProfile(Request $r)
    {
        $owner=$this->owner($r); abort_unless($owner->verification_status === 'verified',403,'Verifikasi akun sebelum mendaftarkan toko.');
        $data=$r->validate([
            'name'=>'required|string|max:100','category'=>'required|string|max:100','description'=>'required|string|min:15|max:2000',
            'address'=>'required|string|max:1000','region_id'=>['required','integer',Rule::exists('regions','id')->whereIn('type',['desa','kelurahan'])],
            'latitude'=>'nullable|required_with:longitude|numeric|between:-90,90','longitude'=>'nullable|required_with:latitude|numeric|between:-180,180',
            'avatar_source'=>'required|in:keep,account,upload','avatar'=>'required_if:avatar_source,upload|nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'cover'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:5120','cover_position'=>'required|integer|between:0,100',
        ]);
        $region=Region::with('parent')->findOrFail($data['region_id']);
        abort_unless($region->parent?->name === 'Kecamatan Bengkalis' && $region->parent?->type === 'kecamatan',422,'Pendaftaran toko saat ini tersedia di Kecamatan Bengkalis.');
        $created=[]; $old=[];
        try { DB::transaction(function() use($owner,$data,$r,$region,&$created,&$old) {
            User::whereKey($owner->id)->lockForUpdate()->firstOrFail();
            $store=MarketStore::where('user_id',$owner->id)->lockForUpdate()->first() ?? new MarketStore;
            $recheck=!$store->exists || $store->status !== 'approved' || $store->name !== $data['name'] || $store->address !== $data['address'] || (int)$store->region_id !== $region->id;
            // Pemilik selalu berasal dari sesi; form tidak boleh memindahkan kepemilikan.
            $store->user_id=$owner->id; $store->region_id=$region->id;
            $store->fill(collect($data)->except(['region_id','avatar','avatar_source','cover'])->all());
            foreach(['avatar','cover'] as $field) if($r->hasFile($field)) {
                $old[]=$store->$field; $store->$field=$created[]=$r->file($field)->store('market_stores','public');
            }
            if($data['avatar_source']==='account' && $owner->avatar) {
                abort_unless(!str_contains($owner->avatar,'..') && !str_starts_with($owner->avatar,'/') && in_array(strtolower(pathinfo($owner->avatar,PATHINFO_EXTENSION)),['jpg','jpeg','png','webp'],true),422,'Gunakan unggahan foto toko.');
                abort_unless(Storage::disk('public')->exists($owner->avatar),422,'Foto akun tidak tersedia. Silakan unggah foto toko.');
                $copy='market_stores/'.\Illuminate\Support\Str::uuid().'.'.pathinfo($owner->avatar,PATHINFO_EXTENSION);
                Storage::disk('public')->copy($owner->avatar,$copy); $old[]=$store->avatar; $store->avatar=$created[]=$copy;
            }
            if(!$store->exists) $store->settings=['enable_cod'=>true,'enable_bank_transfer'=>false,'enable_qris'=>false,
                'market_shipping'=>['pickup'=>true,'delivery'=>false,'village'=>['enabled'=>true,'fee'=>0],'district'=>['enabled'=>false,'fee'=>0],'outside'=>['enabled'=>false,'fee'=>0],'overrides'=>[]]];
            if($recheck) { $store->status='pending'; $store->review_reason=null; $store->reviewed_by=null; $store->reviewed_at=null; }
            $store->save();
            // Produk mengikuti wilayah toko; pesanan lama tetap menyimpan wilayah saat transaksi.
            $store->products()->update(['region_id'=>$region->id]);
            DB::table('market_store_events')->insert(['store_id'=>$store->id,'user_id'=>$owner->id,'action'=>$recheck?'submitted':'profile_updated','created_at'=>now(),'updated_at'=>now()]);
            if($recheck) foreach(User::where('role','admin_desa')->where('region_id',$region->id)->get() as $admin) {
                Notification::create(['user_id'=>$admin->id,'type'=>'status_berubah','title'=>'Pendaftaran Toko Warga',
                    'message'=>$store->name.' menunggu pemeriksaan Anda.','link'=>route('market.review.index'),'is_read'=>false]);
            }
        }); } catch(\Throwable $e) { Storage::disk('public')->delete($created); throw $e; }
        // Foto lama boleh dipakai snapshot pembayaran/riwayat; hanya foto profil milik toko yang diganti.
        foreach(array_filter($old) as $path) if(str_starts_with($path,'market_stores/')) Storage::disk('public')->delete($path);
        return $this->reply($r,'Informasi toko tersimpan. Perubahan nama/alamat dan pengajuan baru diperiksa admin desa.');
    }
    public function settings(Request $r)
    {
        $store=$this->approved($r);
        $data=$r->validate(['is_open'=>'required|boolean','shipping'=>'required|array','enable_cod'=>'required|boolean','enable_bank_transfer'=>'required|boolean',
            'rekening_bank'=>'required_if:enable_bank_transfer,1|nullable|string|max:100','rekening_nomor'=>'required_if:enable_bank_transfer,1|nullable|string|max:50',
            'rekening_nama'=>'required_if:enable_bank_transfer,1|nullable|string|max:100','enable_qris'=>'required|boolean',
            'qris_image'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:3072']);
        abort_unless($data['enable_cod'] || $data['enable_bank_transfer'] || $data['enable_qris'],422,'Pilih setidaknya satu metode pembayaran.');
        $shipping=app(MarketShippingService::class)->validateSettings($data['shipping']);
        $path=null;
        try { DB::transaction(function() use($store,$r,$data,$shipping,&$path) {
            $fresh=MarketStore::lockForUpdate()->findOrFail($store->id); abort_unless($fresh->status==='approved',409,'Status toko berubah.');
            $settings=$fresh->settings ?? [];
            foreach(['enable_cod','enable_bank_transfer','enable_qris'] as $key) $settings[$key]=(bool)$data[$key];
            foreach(['rekening_bank','rekening_nomor','rekening_nama'] as $key) $settings[$key]=$data[$key] ?? null;
            if($r->hasFile('qris_image')) $settings['qris_image']=$path=$r->file('qris_image')->store('store_qris','public');
            abort_unless(!$settings['enable_qris'] || !empty($settings['qris_image']),422,'Unggah QRIS toko sebelum mengaktifkannya.');
            $settings['market_shipping']=$shipping; $fresh->settings=$settings; $fresh->is_open=(bool)$data['is_open']; $fresh->save();
        }); } catch(\Throwable $e) { if($path)Storage::disk('public')->delete($path); throw $e; }
        return $this->reply($r,'Pengiriman dan pembayaran toko diperbarui.');
    }
    public function product(Request $r, ?int $id=null)
    {
        $store=$this->approved($r); $product=$id ? $store->products()->findOrFail($id) : new PasarProduk;
        if($r->isMethod('get')) return view('market.product-form',compact('store','product'));
        $data=$r->validate(['nama_produk'=>'required|string|max:150','deskripsi'=>'required|string|max:4000','harga'=>'required|integer|min:1|max:100000000',
            'stok'=>'required|integer|min:0|max:1000000','satuan'=>'required|string|max:30','kategori'=>'required|string|max:100','status'=>'required|in:tersedia,habis,nonaktif',
            'foto'=>($product->exists?'nullable':'required').'|image|mimes:jpg,jpeg,png,webp|max:3072',
            'foto_2'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:3072','foto_3'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:3072']);
        $paths=[]; $old=[];
        try { DB::transaction(function() use($store,$product,$data,$r,&$paths,&$old) {
            $fresh=MarketStore::lockForUpdate()->findOrFail($store->id);
            abort_unless($fresh->status==='approved',409,'Status toko berubah.');
            $p=$product->exists ? $fresh->products()->lockForUpdate()->findOrFail($product->id) : new PasarProduk;
            $p->fill(collect($data)->except(['foto','foto_2','foto_3'])->all()); $p->store_id=$fresh->id;$p->region_id=$fresh->region_id;
            if((int)$p->stok===0 && $p->status==='tersedia')$p->status='habis';
            foreach(['foto','foto_2','foto_3'] as $field)if($r->hasFile($field)){$old[]=$p->$field;$p->$field=$paths[]=$r->file($field)->store('market_products','public');}
            $newProduct=!$p->exists;
            $p->save();
            if($newProduct && $p->status==='tersedia') \App\Services\NotificationService::broadcastNewProduct('Pasar Daerah — '.$store->name,$p->nama_produk,$p->region_id,route('pasar.show',$p->id));
        }); }catch(\Throwable $e){Storage::disk('public')->delete($paths);throw $e;}
        foreach(array_filter($old) as $path)if(str_starts_with($path,'market_products/'))Storage::disk('public')->delete($path);
        return $this->reply($r,'Produk tersimpan. Pilih Nonaktif untuk menyembunyikannya tanpa menghapus riwayat.');
    }
    public function order(Request $r, int $id)
    {
        $store=$this->owned($r); $order=$store->orders()->with(['items','complaint','user'])->findOrFail($id);
        $payment=DB::table('market_payments')->where('pasar_order_id',$id)->first();
        return $r->expectsJson()?response()->json(compact('order','payment')):view('market.order',compact('store','order','payment'));
    }
    public function changeOrder(Request $r,int $id)
    {
        $owner=$this->owner($r); $data=$r->validate(['action'=>'required|in:accept,send,arrive,cancel,payment_received,refund_done,reject_cancellation','reason'=>'nullable|string|max:1000']);
        app(MarketStoreOrders::class)->change($owner,$id,$data);
        return $r->expectsJson()?response()->json(['message'=>'Pesanan diperbarui.']):back()->with('success','Pesanan diperbarui.');
    }
    public function show(Request $r,int $id)
    {
        $store=MarketStore::where('status','approved')->with(['owner:id,name','region'])->findOrFail($id);
        $products=$store->products()->whereIn('status',['tersedia','habis'])->latest()->paginate(12);
        $reviews=\App\Models\PasarReview::whereHas('produk',fn($q)=>$q->where('store_id',$store->id))->with('user:id,name')->latest()->paginate(10,['*'],'reviews');
        if($r->expectsJson()) return response()->json(['store'=>$store->only(['id','user_id','region_id','name','description','category','address','latitude','longitude','avatar','cover','cover_position','is_open']), 'owner'=>$store->owner,'region'=>$store->region->only(['id','name']), 'products'=>$products,'reviews'=>$reviews]);
        return view('market.store',compact('store','products','reviews'));
    }
    public function reviewIndex(Request $r)
    {
        $admin=$r->user(); abort_unless(in_array($admin?->role,['admin_desa','super_admin'],true)&&$admin->region_id,403);
        $stores=MarketStore::where('region_id',$admin->region_id)->with('owner:id,name')->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")->latest()->paginate(15);
        $complaints=\App\Models\PasarComplaint::where('region_id',$admin->region_id)->where('status','pending')->whereHas('order',fn($q)=>$q->whereNotNull('store_id'))->with('order')->latest()->paginate(10,['*'],'complaints');
        return view('market.review',compact('stores','complaints'));
    }
    public function reviewOrder(Request $r,int $id)
    {
        $admin=$r->user();abort_unless(in_array($admin?->role,['admin_desa','super_admin'],true)&&$admin->region_id,403);
        $order=PasarOrder::where('region_id',$admin->region_id)->whereNotNull('store_id')->whereHas('complaint')->with(['items','complaint','store'])->findOrFail($id);
        return view('market.dispute',compact('order'));
    }
    public function reviewComplaint(Request $r,int $id)
    {
        $data=$r->validate(['status'=>'required|in:approved_refund,approved_replacement,rejected','admin_response'=>'required|string|max:1000']);
        \App\Models\PasarComplaint::where('region_id',$r->user()->region_id)->whereHas('order',fn($q)=>$q->whereNotNull('store_id'))->findOrFail($id);
        app(\App\Services\MarketAfterSalesService::class)->decide($r->user(),$id,$data);
        return back()->with('success','Keputusan komplain tersimpan dan pihak terkait diberi tahu.');
    }
    public function buyerCancel(Request $r,int $id)
    {
        $data=$r->validate(['reason'=>'required|string|max:1000']);
        app(MarketStoreOrders::class)->requestCancellation($r->user(),$id,$data['reason']);
        return response()->json(['message'=>'Permintaan pembatalan dikirim kepada penjual.']);
    }
    public function paymentProof(Request $r,int $id)
    {
        $r->validate(['proof_of_payment'=>'required|file|mimes:jpg,jpeg,png,pdf|max:5120']);
        $path=null;$previous=null;
        try { DB::transaction(function()use($r,$id,&$path,&$previous){
            $order=PasarOrder::where('user_id',$r->user()->id)->lockForUpdate()->findOrFail($id);
            abort_unless($order->status==='pending' && !$order->isCashPayment(),409,'Pesanan ini tidak lagi menunggu bukti pembayaran.');
            $previous=$order->proof_of_payment;
            $order->proof_of_payment=$path=$r->file('proof_of_payment')->store('payment_proofs/pasar','public');$order->save();
            if($order->store_id) Notification::create(['user_id'=>$order->store->user_id,'type'=>'payment','title'=>'Bukti Pembayaran Dikirim',
                'message'=>'Periksa pembayaran pesanan '.$order->order_number.'.','link'=>route('market.seller.order',$order->id),'is_read'=>false]);
            else {
                DB::table('wallet_transactions')->where('reference_type','pasar')->where('reference_id',$id)->where('status','pending')->update(['proof_path'=>$path,'updated_at'=>now()]);
                \App\Models\AdminNotification::create(['region_id'=>$order->region_id,'type'=>'pasar_order','reference_id'=>$id,'title'=>'Bukti Pembayaran Dikirim','message'=>'Periksa pembayaran '.$order->order_number.'.','is_read'=>false]);
            }
        }); } catch(\Throwable $e){if($path)Storage::disk('public')->delete($path);throw $e;}
        if($previous && $previous!==$path && str_starts_with($previous,'payment_proofs/pasar/')) Storage::disk('public')->delete($previous);
        return $r->expectsJson()?response()->json(['message'=>'Bukti terkirim. Penjual akan memeriksa pembayaran Anda.']):back()->with('success','Bukti terkirim. Penjual akan memeriksa pembayaran Anda.');
    }
    public function review(Request $r,int $id)
    {
        $admin=$r->user(); abort_unless(in_array($admin?->role,['admin_desa','super_admin'],true)&&$admin->region_id,403);
        $data=$r->validate(['decision'=>'required|in:approved,rejected,suspended','reason'=>'required_unless:decision,approved|nullable|string|max:1000']);
        DB::transaction(function()use($admin,$data,$id){
            $store=MarketStore::where('region_id',$admin->region_id)->lockForUpdate()->findOrFail($id);
            abort_unless($data['decision']==='suspended' ? $store->status==='approved' : $store->status==='pending',409,'Pengajuan sudah ditangani atau status berubah.');
            abort_unless($data['decision']!=='approved' || ($store->owner->role==='user' && $store->owner->verification_status==='verified'),422,'Pemilik harus warga terverifikasi.');
            $store->status=$data['decision'];$store->review_reason=$data['reason']??null;$store->reviewed_by=$admin->id;$store->reviewed_at=now();$store->save();
            DB::table('market_store_events')->insert(['store_id'=>$id,'user_id'=>$admin->id,'action'=>$store->status,'note'=>$store->review_reason,'created_at'=>now(),'updated_at'=>now()]);
            $labels=['approved'=>'disetujui. Anda dapat menambahkan produk pertama.','rejected'=>'ditolak. '.$store->review_reason,'suspended'=>'dinonaktifkan. '.$store->review_reason];
            Notification::create(['user_id'=>$store->user_id,'type'=>'status_berubah','title'=>'Status Toko Anda','message'=>'Toko '.$store->name.' '.$labels[$store->status],'link'=>route('market.seller.home'),'is_read'=>false]);
        });
        return back()->with('success','Keputusan toko tersimpan dan pemilik mendapat notifikasi.');
    }
    public function chat(Request $r,int $id,?int $buyer=null)
    {
        $store=MarketStore::findOrFail($id);$user=$r->user();
        $buyer=$buyer ?? ($store->user_id===$user->id ? null : $user->id);
        abort_unless($buyer && ($store->user_id===$user->id || $buyer===$user->id),403);
        abort_if($buyer===$store->user_id,422,'Pilih percakapan pembeli.');
        $messages=DB::table('market_store_messages')->where('store_id',$id)->where('buyer_id',$buyer)->orderByDesc('id')->limit(100)->get()->reverse()->values();
        abort_unless(User::whereKey($buyer)->exists(),404);
        if($store->user_id===$user->id) abort_unless($messages->isNotEmpty() || $store->orders()->where('user_id',$buyer)->exists(),404,'Percakapan pembeli tidak ditemukan.');
        if($r->isMethod('post')){
            abort_unless($store->status==='approved' || $messages->isNotEmpty(),403,'Toko belum menerima percakapan baru.');
            $data=$r->validate(['message'=>'required|string|max:2000']);
            DB::table('market_store_messages')->insert(['store_id'=>$id,'buyer_id'=>$buyer,'sender_id'=>$user->id,'message'=>$data['message'],'created_at'=>now(),'updated_at'=>now()]);
            $recipient=$store->user_id===$user->id?$buyer:$store->user_id;
            Notification::create(['user_id'=>$recipient,'type'=>'status_berubah','title'=>'Pesan Toko '.$store->name,'message'=>'Ada pesan baru untuk Anda.','link'=>route('market.chat',[$id,$buyer]),'is_read'=>false]);
            return $r->expectsJson()?response()->json(['message'=>'Pesan terkirim.'],201):back()->with('success','Pesan terkirim.');
        }
        abort_unless($store->status==='approved' || $messages->isNotEmpty(),404);
        if($r->expectsJson())return response()->json(['messages'=>$messages]);
        return view('market.chat',compact('store','buyer','messages'));
    }
}
