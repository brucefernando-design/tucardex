<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class StudentDocumentController extends Controller
{
    public function constancia(Student $student): Response
    {
        $student->load('course.tutor');
        $setting = Setting::current();

        $pdf = Pdf::loadView('documents.constancia', compact('student', 'setting'))
            ->setPaper('letter', 'portrait');

        return $pdf->download('Constancia_'.str_replace(' ', '_', $student->full_name).'.pdf');
    }

    public function estadoCuenta(Student $student): Response
    {
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
        $student->load('course');
        $setting = Setting::current();

        // Tamaño tipo credencial (85.6 x 54 mm)
        $pdf = Pdf::loadView('documents.carnet', compact('student', 'setting'))
            ->setPaper([0, 0, 242.65, 153.07], 'portrait');

        return $pdf->download('Credencial_'.str_replace(' ', '_', $student->full_name).'.pdf');
    }
}
