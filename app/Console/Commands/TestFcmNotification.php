<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestFcmNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:test {email} {--title=Test Notifikasi} {--body=Ini adalah pesan percobaan dari sistem Sila-DesBeng}';
    protected $description = 'Kirim test push notification ke user spesifik via email';

    public function handle()
    {
        $email = $this->argument('email');
        $title = $this->option('title');
        $body = $this->option('body');

        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            $this->error("User dengan email {$email} tidak ditemukan.");
            return;
        }

        if (empty($user->fcm_token)) {
            $this->error("User {$user->name} belum memiliki FCM Token (Belum login di Flutter).");
            return;
        }

        $this->info("Mengirim notifikasi ke {$user->name}...");

        $service = new \App\Services\FirebaseNotificationService();
        $success = $service->sendPushNotification($user->fcm_token, $title, $body, [
            'type' => 'test',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
        ]);

        if ($success) {
            $this->info("Notifikasi berhasil dikirim!");
        } else {
            $this->error("Gagal mengirim notifikasi. Cek log laravel untuk detail.");
        }
    }
}
