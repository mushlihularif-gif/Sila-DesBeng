<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable implements ShouldQueue
{
    /**
     * Dikirim lewat antrean, bukan saat request berjalan.
     *
     * Sebelumnya pendaftaran menahan warga sampai SMTP menjawab —
     * 1-3 detik kalau lancar, dan menggantung sampai timeout kalau
     * penyedia email melambat. Padahal akunnya sudah terbuat, sehingga
     * warga mengira gagal lalu mendaftar ulang.
     *
     * SYARAT: worker harus berjalan (cron queue:work). Selama
     * QUEUE_CONNECTION=sync, ini tetap berperilaku seperti dulu.
     */
    use Queueable, SerializesModels;

    public $otp;

    /**
     * Create a new message instance.
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi (OTP) - SiladesBeng',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

