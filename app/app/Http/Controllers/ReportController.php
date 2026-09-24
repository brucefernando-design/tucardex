<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $studentsByCourse = Course::withCount('students')->orderBy('name')->get();

        $paymentsByStatus = [
            'pagado' => (float) Payment::where('status', 'pagado')->sum('amount'),
            'pendiente' => (float) Payment::where('status', 'pendiente')->sum('amount'),
            'vencido' => (float) Payment::where('status', 'vencido')->sum('amount'),
        ];

        $totals = [
            'students' => Student::count(),
            'active' => Student::where('status', 'activo')->count(),
            'income' => (float) Payment::where('status', 'pagado')->sum('amount'),
        ];

        return view('reports.index', compact('studentsByCourse', 'paymentsByStatus', 'totals'));
    }
}
