<?php

namespace App\Http\Controllers;

use App\Models\{AdminNotification, Notification};
use App\Services\{FonnteService, NotificationDeliveryService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Validation\Rule;

class NotificationChannelController extends Controller
{
    public function __construct(private NotificationDeliveryService $delivery) {}

    public function index(Request $request)
    {
        $settings=$this->delivery->settings($request->user());
        $categories=json_decode($settings?->categories ?? '{}',true) ?: [];
        $verified=$settings?->verified_phone_hash && hash_equals($settings->verified_phone_hash,$this->delivery->phoneHash($request->user()->phone));
        $history=DB::table('notification_deliveries')->where('user_id',$request->user()->id)->latest('id')->limit(30)->get(['category','channel','state','error_code','created_at','updated_at']);
        if ($request->wantsJson()) return response()->json(['categories'=>$categories,'push_enabled'=>$settings?->push_enabled ?? true,'phone_verified'=>(bool)$verified,'history'=>$history]);
        return view('notifications.channels',compact('settings','categories','verified','history'));
    }

    public function update(Request $request)
    {
        $data=$request->validate([
            'categories'=>['required','array:'.implode(',',array_keys(config('notification_channels.categories')))],
            'categories.*'=>[Rule::in(['off','instant','daily'])], 'consent'=>'nullable|boolean','push_enabled'=>'nullable|boolean',
        ]);
        $user=$request->user(); $settings=$this->delivery->settings($user);
        foreach ($data['categories'] as $category=>$mode) {
            abort_if($mode === 'daily' && !in_array($category,['news','market'],true),422,'Ringkasan harian hanya tersedia untuk kabar dan produk baru.');
        }
        $whatsapp=collect($data['categories'])->contains(fn ($mode) => $mode !== 'off');
        if ($whatsapp) {
            abort_unless($request->boolean('consent') && $settings?->verified_phone_hash
                && hash_equals($settings->verified_phone_hash,$this->delivery->phoneHash($user->phone)),422,'Verifikasi nomor WhatsApp dan centang persetujuan terlebih dahulu.');
        }
        DB::table('notification_settings')->updateOrInsert(['user_id'=>$user->id],[
            'categories'=>json_encode($data['categories']),'push_enabled'=>$request->boolean('push_enabled'),
            'consented_at'=>$whatsapp ? ($settings?->consented_at ?? now()) : null,'updated_at'=>now(),'created_at'=>$settings?->created_at ?? now(),
        ]);
        if ($request->wantsJson()) return response()->json(['message'=>'Pilihan notifikasi disimpan.']);
        return back()->with('success','Pilihan notifikasi disimpan.');
    }

    public function sendCode(Request $request, FonnteService $fonnte)
    {
        $user=$request->user();
        abort_unless($user->phone && preg_match('/^\+?[\d\s-]{8,20}$/',$user->phone),422,'Lengkapi nomor WhatsApp pada profil terlebih dahulu.');
        $code=(string)random_int(100000,999999);
        DB::table('notification_settings')->updateOrInsert(['user_id'=>$user->id],[
            'otp_hash'=>Hash::make($code),'otp_phone_hash'=>$this->delivery->phoneHash($user->phone),
            'otp_expires_at'=>now()->addMinutes(5),'otp_attempts'=>0,'updated_at'=>now(),
        ]);
        $result=$fonnte->sendOtp($user->phone,$code);
        if (!$result['status']) {
            DB::table('notification_settings')->where('user_id',$user->id)->update(['otp_hash'=>null,'otp_expires_at'=>null]);
            if ($request->wantsJson()) return response()->json(['message'=>$result['message']],503);
            return back()->with('error',$result['message']);
        }
        if ($request->wantsJson()) return response()->json(['message'=>'Kode verifikasi telah diajukan untuk dikirim. Berlaku lima menit.']);
        return back()->with('success','Kode verifikasi dikirim ke nomor pada profil. Berlaku lima menit.');
    }

    public function verifyCode(Request $request)
    {
        $data=$request->validate(['code'=>'required|digits:6']); $user=$request->user();
        $ok=DB::transaction(function () use ($user,$data) {
            $settings=DB::table('notification_settings')->where('user_id',$user->id)->lockForUpdate()->first();
            if (!$settings?->otp_hash || $settings->otp_attempts >= 5 || !$settings->otp_expires_at
                || now()->gte($settings->otp_expires_at) || !hash_equals($settings->otp_phone_hash,$this->delivery->phoneHash($user->phone))) return false;
            DB::table('notification_settings')->where('user_id',$user->id)->increment('otp_attempts');
            if (!Hash::check($data['code'],$settings->otp_hash)) return false;
            DB::table('notification_settings')->where('user_id',$user->id)->update([
                'verified_phone_hash'=>$settings->otp_phone_hash,'otp_hash'=>null,'otp_phone_hash'=>null,'otp_expires_at'=>null,'updated_at'=>now(),
            ]);
            return true;
        });
        if (!$ok) {
            if ($request->wantsJson()) return response()->json(['message'=>'Kode salah atau kedaluwarsa.'],422);
            return back()->with('error','Kode salah atau kedaluwarsa.');
        }
        if ($request->wantsJson()) return response()->json(['message'=>'Nomor berhasil diverifikasi.']);
        return back()->with('success','Nomor berhasil diverifikasi. Pilih kategori pemberitahuan yang Anda inginkan.');
    }

    public function open(Request $request, string $kind, int $notification)
    {
        if (!$request->user()) {
            if ($request->wantsJson()) return response()->json(['message'=>'Silakan masuk untuk melihat pemberitahuan.'],401);
            $request->session()->put('url.intended',$request->url());
            return redirect()->route('beranda')->with('open_login_modal',true);
        }
        abort_unless(in_array($kind,['user','admin'],true),404);
        abort_if($kind === 'admin' && !$request->user()->isAdmin(),404);
        $source=$kind === 'user' ? Notification::where('user_id',$request->user()->id)->findOrFail($notification)
            : AdminNotification::untukPengguna($request->user())->findOrFail($notification);
        $entry=(object)['source_kind'=>$kind,'source_id'=>$notification,'category'=>$this->delivery->category($source->type ?? '')];
        abort_unless($this->delivery->source($entry,$request->user()),404);
        $url=$this->delivery->destination($source);
        if ($request->wantsJson()) return response()->json(['title'=>$source->title,'message'=>$source->message,'destination'=>$url,'type'=>$source->type]);
        return redirect($url);
    }

    public function webhook(Request $request)
    {
        $secret=config('notification_channels.webhook_secret');
        abort_unless($secret && hash_equals($secret,(string)($request->header('X-Webhook-Token') ?: $request->query('token',''))),403);
        $data=$request->validate(['id'=>'required|string|max:255','status'=>'nullable|string|max:40','state'=>'nullable|string|max:40']);
        $state=strtolower($data['state'] ?? $data['status'] ?? '');
        $mapped=match($state) {'sent'=>'sent','delivered'=>'delivered','read'=>'read','failed','invalid','expired'=>'failed',default=>null};
        if ($mapped) DB::transaction(function () use ($data,$mapped) {
            $rows=DB::table('notification_deliveries')->where('provider_id',$data['id'])->where('channel','whatsapp')->lockForUpdate()->get();
            $rank=['accepted'=>0,'unknown'=>0,'sent'=>1,'delivered'=>2,'read'=>3,'failed'=>-1];
            foreach ($rows as $row) {
                if (in_array($row->state,['cancelled','pending','processing'])) continue;
                if (($rank[$row->state] ?? 0) >= 2 && $mapped === 'failed') continue;
                if ($mapped !== 'failed' && ($rank[$mapped] ?? 0) < ($rank[$row->state] ?? 0)) continue;
                DB::table('notification_deliveries')->where('id',$row->id)->update([
                    'state'=>$mapped,'error_code'=>$mapped === 'failed' ? 'provider_failed':null,
                    'delivered_at'=>in_array($mapped,['delivered','read']) ? ($row->delivered_at ?? now()):$row->delivered_at,'updated_at'=>now(),
                ]);
            }
        });
        return response()->json(['ok'=>true]);
    }

    public function assetLinks()
    {
        $package=config('notification_channels.android_package'); $fingerprints=config('notification_channels.android_fingerprints');
        abort_unless($package && $fingerprints,404);
        foreach ($fingerprints as $fingerprint) abort_unless(preg_match('/^(?:[A-Fa-f0-9]{2}:){31}[A-Fa-f0-9]{2}$/',$fingerprint),503);
        return response()->json([['relation'=>['delegate_permission/common.handle_all_urls'],'target'=>[
            'namespace'=>'android_app','package_name'=>$package,'sha256_cert_fingerprints'=>$fingerprints,
        ]]])->header('Cache-Control','public, max-age=3600');
    }
}
