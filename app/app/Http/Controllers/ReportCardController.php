<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportCardController extends Controller
{
    private array $periods = ['1er Trimestre', '2do Trimestre', '3er Trimestre'];

    /**
     * Genera y descarga el boletín de notas del estudiante en PDF.
     */
    public function pdf(Student $student): Response
    {
        $data = $this->buildData($student);

        $pdf = Pdf::loadView('reportcards.boletin', $data)
            ->setPaper('letter', 'portrait');

        $filename = 'Boleta_'.str_replace(' ', '_', $student->full_name).'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Vista previa en HTML (mismo contenido que el PDF).
     */
    public function preview(Student $student)
    {
        return view('reportcards.boletin', $this->buildData($student));
    }

    /**
     * Acta consolidada de calificaciones de todo un curso, con promedio y puesto.
     */
    public function courseSheet(Request $request, Course $course): Response
    {
        $period = $request->period ?: 'Todos';

        $course->load(['tutor', 'subjects' => fn ($q) => $q->orderBy('name')]);
        $subjects = $course->subjects;

        $students = Student::where('course_id', $course->id)
            ->where('status', 'activo')
            ->orderBy('last_name')->get();

        // Promedios por estudiante y materia
        $gradesQuery = Grade::where('course_id', $course->id)
            ->when($period !== 'Todos', fn ($q) => $q->where('period', $period))
            ->get()
            ->groupBy('student_id');

        $rows = [];
        foreach ($students as $s) {
            $studentGrades = $gradesQuery->get($s->id, collect());
            $bySubject = [];
            $sum = 0;
            $count = 0;
            foreach ($subjects as $sub) {
                $g = $studentGrades->where('subject_id', $sub->id);
                $avg = $g->count() ? round($g->avg('score'), 1) : null;
                $bySubject[$sub->id] = $avg;
                if ($avg !== null) {
                    $sum += $avg;
                    $count++;
                }
            }
            $overall = $count ? round($sum / $count, 1) : null;
            $rows[] = ['student' => $s, 'bySubject' => $bySubject, 'overall' => $overall];
        }

        // Ranking por promedio (los que tienen nota)
        $ranked = collect($rows)->filter(fn ($r) => $r['overall'] !== null)->sortByDesc('overall')->values();
        $rankMap = [];
        foreach ($ranked as $i => $r) {
            $rankMap[$r['student']->id] = $i + 1;
        }
        foreach ($rows as &$r) {
            $r['rank'] = $rankMap[$r['student']->id] ?? '—';
        }
        unset($r);

        $setting = Setting::current();

        $pdf = Pdf::loadView('reportcards.course_sheet', compact('course', 'subjects', 'rows', 'period', 'setting'))
            ->setPaper('legal', 'landscape');

        return $pdf->download('Acta_'.str_replace(' ', '_', $course->name).'_'.$course->section.'.pdf');
    }

    private function buildData(Student $student): array
    {
        $student->load(['course.tutor', 'grades.subject', 'attendances']);

        // Calcular % de asistencia (mínimo SEP: 80%)
        $attendances = $student->attendances;
        $totalDays = $attendances->count();
        $absentStatuses = ['falta', 'ausente', 'inasistencia', 'f', 'no_asistio', 'absent'];
        $presentDays = $attendances->filter(
            fn ($a) => !in_array(strtolower($a->status ?? ''), $absentStatuses)
        )->count();
        $attendancePercent = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : null;
        $attendanceMeetsRequirement = $attendancePercent !== null && $attendancePercent >= 80.0;

        // Agrupar notas por materia y calcular promedio por periodo
        $rows = [];
        $grouped = $student->grades->groupBy('subject_id');

        foreach ($grouped as $subjectId => $grades) {
            $subject = $grades->first()->subject;
            $periodAverages = [];
            $sumPeriods = 0;
            $countPeriods = 0;

            foreach ($this->periods as $period) {
                $periodGrades = $grades->where('period', $period);
                if ($periodGrades->count()) {
                    $avg = round($periodGrades->avg('score'), 1);
                    $periodAverages[$period] = $avg;
                    $sumPeriods += $avg;
                    $countPeriods++;
                } else {
                    $periodAverages[$period] = null;
                }
            }

            $final = $countPeriods ? round($sumPeriods / $countPeriods, 1) : null;

            $rows[] = [
                'subject' => $subject->name,
                'periods' => $periodAverages,
                'final' => $final,
                'status' => $final === null ? '—' : ($final >= 6.0 ? 'Aprobado' : 'Reprobado'),
            ];
        }

        // Promedio general
        $finals = array_filter(array_column($rows, 'final'), fn ($v) => $v !== null);
        $generalAverage = count($finals) ? round(array_sum($finals) / count($finals), 1) : null;

        return [
            'student'                    => $student,
            'rows'                       => $rows,
            'periods'                    => $this->periods,
            'generalAverage'             => $generalAverage,
            'attendancePercent'          => $attendancePercent,
            'attendanceMeetsRequirement' => $attendanceMeetsRequirement,
            'totalDays'                  => $totalDays,
            'date'                       => now(),
        ];
    }
}
