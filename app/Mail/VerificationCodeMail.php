<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code; // متغير عام لاستقبال الكود

    // 1. تمرير الكود عبر الـ Constructor
    public function __construct($code)
    {
        $this->code = $code;
    }

    // 2. تحديد عنوان الرسالة
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'كود التحقق الخاص بك',
        );
    }

    // 3. تحديد ملف الـ View الذي صممناه
    public function content(): Content
    {
        return new Content(
            view: 'emails.verification_code',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}