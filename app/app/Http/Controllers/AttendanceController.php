<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Setting;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::orderBy('name')->get();
        $date = $request->date ?: now()->toDateString();
        $courseId = $request->course_id ?: optional($courses->first())->id;

        $students = collect();
        $existing = collect();

        if ($courseId) {
            $students = Student::where('course_id', $courseId)
                ->where('status', 'activo')
                ->orderBy('last_name')->get();

            $existing = Attendance::where('course_id', $courseId)
                ->whereDate('date', $date)
                ->get()
                ->keyBy('student_id');
        }

        return view('attendances.index', compact('courses', 'students', 'existing', 'date', 'courseId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'array'],
        ]);

        foreach ($request->status as $studentId => $status) {
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $request->date],
                [
                    'course_id' => $request->course_id,
                    'status' => $status,
                    'remarks' => $request->input("remarks.$studentId"),
                ]
            );
        }

        return redirect()->route('attendances.index', ['course_id' => $request->course_id, 'date' => $request->date])
            ->with('success', 'Asistencia registrada correctamente.');
    }

    /**
     * Reporte mensual de asistencia por curso.
     */
    public function report(Request $request): View
    {
        $courses = Course::orderBy('name')->get();
        $courseId = $request->course_id ?: optional($courses->first())->id;
        $month = $request->month ?: now()->format('Y-m');

        $data = $this->buildMonthlyData($courseId, $month);

        return view('attendances.report', array_merge($data, compact('courses', 'courseId', 'month')));
    }

    public function reportPdf(Request $request)
    {
        $courseId = $request->course_id;
        $month = $request->month ?: now()->format('Y-m');
        $data = $this->buildMonthlyData($courseId, $month);
        $course = Course::find($courseId);
        $setting = Setting::current();

        $pdf = Pdf::loadView('exports.attendance_pdf', array_merge($data, compact('course', 'month', 'setting')))
            ->setPaper('legal', 'landscape');

        return $pdf->download('Asistencia_'.$month.'.pdf');
    }

    /**
     * Construye la matriz de asistencia del mes: días, estudiantes y resumen.
     */
    private function buildMonthlyData(?int $courseId, string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        // Días hábiles del mes (lunes a viernes)
        $days = [];
        for ($d = clone $start; $d <= $end; $d->addDay()) {
            if (! $d->isWeekend()) {
                $days[] = $d->day;
            }
        }

        $students = collect();
        $matrix = [];
        $summary = [];

        if ($courseId) {
            $students = Student::where('course_id', $courseId)
                ->where('status', 'activo')
                ->orderBy('last_name')->get();

            $records = Attendance::where('course_id', $courseId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get();

            // matrix[studentId][day] = inicial del estado
            $map = ['presente' => 'P', 'ausente' => 'F', 'tardanza' => 'T', 'justificado' => 'J'];
            foreach ($records as $r) {
                $day = Carbon::parse($r->date)->day;
                $matrix[$r->student_id][$day] = $map[$r->status] ?? '-';
            }

            foreach ($students as $s) {
                $counts = ['P' => 0, 'F' => 0, 'T' => 0, 'J' => 0];
                foreach ($days as $day) {
                    $mark = $matrix[$s->id][$day] ?? null;
                    if ($mark && isset($counts[$mark])) {
                        $counts[$mark]++;
                    }
                }
                $registered = array_sum($counts);
                $attended = $counts['P'] + $counts['T'] + $counts['J'];
                $summary[$s->id] = [
                    'counts' => $counts,
                    'registered' => $registered,
                    'pct' => $registered ? round($attended / $registered * 100) : 0,
                ];
            }
        }

        return compact('days', 'students', 'matrix', 'summary', 'start');
    }
}
