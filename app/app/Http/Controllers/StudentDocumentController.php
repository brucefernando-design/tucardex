<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class StudentDocumentController extends Controller
{
    /**
     * Valida permisos para consultar documentos del estudiante.
     * Admin, secretaria, docente: acceso total.
     * Padre: solo sus hijos vinculados.
     * Estudiante: solo su propio expediente (y NO estado de cuenta).
     */
    private function authorizeAccess(Student $student, bool $allowStudent = true): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->hasAnyRole(['admin', 'secretaria', 'docente', 'superadmin'])) {
            return;
        }

        if ($user->hasRole('padre')) {
            if (! $student->guardians()->where('users.id', $user->id)->exists()) {
                abort(403, 'No tienes autorización para acceder a los documentos de este estudiante.');
            }
            return;
        }

        if ($user->hasRole('estudiante')) {
            if (! $allowStudent) {
                abort(403, 'Los estudiantes no tienen acceso al estado de cuenta financiero.');
            }
            if ($student->user_id !== $user->id) {
                abort(403, 'No tienes autorización para acceder a este expediente.');
            }
            return;
        }

        abort(403, 'Acceso denegado.');
    }

    public function constancia(Student $student): Response
    {
        $this->authorizeAccess($student, true);
        $student->load('course.tutor');
        $setting = Setting::current();

        $pdf = Pdf::loadView('documents.constancia', compact('student', 'setting'))
            ->setPaper('letter', 'portrait');

        return $pdf->download('Constancia_'.str_replace(' ', '_', $student->full_name).'.pdf');
    }

    public function estadoCuenta(Student $student): Response
    {
        $this->authorizeAccess($student, false);
        $student->load(['course', 'payments' => fn ($q) => $q->orderBy('due_date')]);
        $setting = Setting::current();

        $totals = [
            'pagado' => (float) $student->payments->where('status', 'pagado')->sum('amount'),
            'pendiente' => (float) $student->payments->where('status', 'pendiente')->sum('amount'),
            'vencido' => (float) $student->payments->where('status', 'vencido')->sum('amount'),
        ];
        $totals['saldo'] = $totals['pendiente'] + $totals['vencido'];
        $totals['total'] = (float) $student->payments->whereNotIn('status', ['anulado'])->sum('amount');

        $pdf = Pdf::loadView('documents.estado_cuenta', compact('student', 'setting', 'totals'))
            ->setPaper('letter', 'portrait');

        return $pdf->download('EstadoCuenta_'.str_replace(' ', '_', $student->full_name).'.pdf');
    }

    public function carnet(Student $student): Response
    {
        $this->authorizeAccess($student, true);
        $student->load('course');
        $setting = Setting::current();

        $pdf = Pdf::loadView('documents.carnet', compact('student', 'setting'))
            ->setPaper([0, 0, 242.65, 153.07], 'portrait');

        return $pdf->download('Credencial_'.str_replace(' ', '_', $student->full_name).'.pdf');
    }
}
