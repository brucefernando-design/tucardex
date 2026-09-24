<?php

namespace App\Http\Controllers;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::with('author')->latest()->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        return view('announcements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['author_id'] = $request->user()->id;
        $data['published_at'] = $data['status'] === 'publicado' ? now() : null;

        $announcement = Announcement::create($data);

        $msg = 'Comunicado publicado.';
        if ($announcement->status === 'publicado') {
            $this->notifyAudience($announcement);
            if ($request->boolean('send_email')) {
                $msg .= ' '.$this->sendEmails($announcement);
            }
        }

        return redirect()->route('announcements.index')->with('success', $msg);
    }

    public function edit(Announcement $announcement): View
    {
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['published_at'] = $data['status'] === 'publicado'
            ? ($announcement->published_at ?? now())
            : null;

        $announcement->update($data);

        $msg = 'Comunicado actualizado.';
        if ($request->boolean('send_email') && $announcement->status === 'publicado') {
            $msg .= ' '.$this->sendEmails($announcement);
        }

        return redirect()->route('announcements.index')->with('success', $msg);
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Comunicado eliminado.');
    }

    /**
     * Reenvía un comunicado ya publicado por correo.
     */
    public function resend(Announcement $announcement): RedirectResponse
    {
        return redirect()->route('announcements.index')->with('success', $this->sendEmails($announcement));
    }

    /**
     * Envía el comunicado por correo a los destinatarios según la audiencia.
     */
    private function sendEmails(Announcement $announcement): string
    {
        $emails = $this->recipients($announcement->audience);

        if ($emails->isEmpty()) {
            return 'No se encontraron correos para esta audiencia.';
        }

        $setting = Setting::current();
        $from = config('mail.from.address');

        // Un solo envío con copia oculta a todos los destinatarios.
        Mail::to($from)->bcc($emails->all())->send(new AnnouncementMail($announcement, $setting->school_name));

        return 'Enviado por correo a '.$emails->count().' destinatario(s).';
    }

    /**
     * Crea notificaciones in-app para los usuarios de la audiencia.
     */
    private function notifyAudience(Announcement $announcement): void
    {
        $slugs = match ($announcement->audience) {
            'docentes' => ['docente'],
            'estudiantes', 'padres' => ['estudiante'],
            default => ['admin', 'secretaria', 'docente', 'estudiante'],
        };

        $userIds = User::whereHas('role', fn ($q) => $q->whereIn('slug', $slugs))
            ->where('id', '!=', auth()->id())
            ->pluck('id');

        foreach ($userIds as $uid) {
            Notification::notify(
                $uid,
                'Nuevo comunicado: '.$announcement->title,
                'announcement',
                \Illuminate\Support\Str::limit($announcement->body, 80),
                route('announcements.index')
            );
        }
    }

    private function recipients(string $audience)
    {
        $emails = collect();

        if (in_array($audience, ['docentes', 'todos'])) {
            $emails = $emails
                ->merge(Teacher::whereNotNull('email')->pluck('email'))
                ->merge(User::whereHas('role', fn ($q) => $q->where('slug', 'docente'))->pluck('email'));
        }

        if (in_array($audience, ['estudiantes', 'padres', 'todos'])) {
            $emails = $emails->merge(Student::whereNotNull('email')->pluck('email'));
        }

        if ($audience === 'todos') {
            $emails = $emails->merge(User::pluck('email'));
        }

        return $emails->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))->unique()->values();
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
            'audience' => ['required', 'in:todos,docentes,estudiantes,padres'],
            'status' => ['required', 'in:borrador,publicado'],
        ]);
    }
}
