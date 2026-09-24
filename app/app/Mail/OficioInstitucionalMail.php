<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OficioInstitucionalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tipo,
        public string $asunto,
        public string $cuerpo,
        public string $destinatario,
        public ?string $fechaCita,
        public ?string $horaCita,
        public ?string $lugar,
        public string $firmante,
        public string $cargo,
        public string $schoolName,
        public ?string $pdfContent = null,
        public ?string $pdfFilename = 'Oficio_Oficial.pdf'
    ) {}

    public function envelope(): Envelope
    {
        $prefix = match ($this->tipo) {
            'citatorio' => '[CITATORIO ESCOLAR]',
            'justificante' => '[JUSTIFICANTE ESCOLAR]',
            default => '[COMUNICADO OFICIAL]',
        };

        return new Envelope(
            subject: "{$prefix} {$this->asunto} - {$this->schoolName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.oficio',
        );
    }

    public function attachments(): array
    {
        if ($this->pdfContent) {
            return [
                Attachment::fromData(fn () => $this->pdfContent, $this->pdfFilename)
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
