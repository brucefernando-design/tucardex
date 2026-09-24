<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment
    ) {}

    public function envelope(): Envelope
    {
        $appSettings = Setting::current();
        $schoolName = $appSettings->school_name ?: 'Colegio';
        $student = $this->payment->student;
        $studentName = $student ? $student->full_name : 'Alumno';
        $concepto = $this->payment->concept ?: 'Colegiatura';

        return new Envelope(
            subject: "Recordatorio de Pago: {$concepto} - {$studentName} · {$schoolName}",
        );
    }

    public function content(): Content
    {
        $appSettings = Setting::current();
        return new Content(
            view: 'emails.payment_reminder',
            with: [
                'payment' => $this->payment,
                'student' => $this->payment->student,
                'settings' => $appSettings,
                'checkoutUrl' => route('parent.payments.checkout', $this->payment),
            ],
        );
    }
}
