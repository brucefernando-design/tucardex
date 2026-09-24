<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ReportCardController extends Controller
{
    private array $periods = ['1er Trimestre', '2do Trimestre', '3er Trimestre'];

    /**
     * Genera y descarga el boletín de notas del estudiante en PDF.
     */
    /**
     * Valida permisos para consultar la boleta del estudiante.
     * Admin, secretaria, docente: acceso total.
     * Padre: solo sus hijos vinculados.
     * Estudiante: solo su propio expediente.
     */
    /**
     * Valida permisos para consultar la boleta del estudiante:
     * - Admin / Secretaría / SuperAdmin: acceso institucional.
     * - Docente: únicamente si es tutor del grupo del alumno o le imparte clases.
     * - Padre: solo sus hijos vinculados.
     * - Estudiante: solo su propio expediente.
     */
    private function authorizeAccess(Student $student): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->hasRole('superadmin')) {
            return;
        }

        if ($user->hasAnyRole(['admin', 'secretaria'])) {
            if ($user->school_id !== $student->school_id) {
                abort(403, 'No perteneces a la institución de este estudiante.');
            }
            return;
        }

        if ($user->hasRole('docente')) {
            $teacher = $user->teacher;
            if (! $teacher || ! $this->teacherHasAccessToStudent($teacher, $student)) {
                abort(403, 'No tienes autorización para consultar la boleta de un estudiante de otro grupo.');
            }
            return;
        }

        if ($user->hasRole('padre')) {
            if (! $student->guardians()->where('users.id', $user->id)->exists()) {
                abort(403, 'No tienes autorización para acceder a la boleta de este estudiante.');
            }
            return;
        }

        if ($user->hasRole('estudiante')) {
            if ($student->user_id !== $user->id) {
                abort(403, 'No tienes autorización para acceder a la boleta de este estudiante.');
            }
            return;
        }

        abort(403, 'Acceso denegado.');
    }

    private function teacherHasAccessToCourse(Teacher $teacher, Course $course): bool
    {
        if ($course->tutor_id === $teacher->id) {
            return true;
        }

        if (DB::table('course_subject')->where('course_id', $course->id)->where('teacher_id', $teacher->id)->exists()) {
            return true;
        }

        if ($course->schedules()->where('teacher_id', $teacher->id)->exists()) {
            return true;
        }

        return false;
    }

    private function teacherHasAccessToStudent(Teacher $teacher, Student $student): bool
    {
        if (! $student->course) {
            return false;
        }

        return $this->teacherHasAccessToCourse($teacher, $student->course);
    }

    public function pdf(Student $student): Response
    {
        $this->authorizeAccess($student);
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
        $this->authorizeAccess($student);
        return view('reportcards.boletin', $this->buildData($student));
    }

    /**
     * Acta consolidada de calificaciones de todo un curso, con promedio y puesto.
     */
    public function courseSheet(Request $request, Course $course): Response
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->hasRole('docente')) {
            $teacher = $user->teacher;
            if (! $teacher || ! $this->teacherHasAccessToCourse($teacher, $course)) {
                abort(403, 'No tienes autorización para consultar el acta de un grupo que no tienes asignado.');
            }
        } elseif (! $user->hasAnyRole(['admin', 'secretaria', 'superadmin'])) {
            abort(403, 'Acceso no autorizado.');
        }

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

    /**
     * Genera y descarga las boletas oficiales del grupo en un solo archivo PDF compilado.
     * Optimizado: partición en bloques de máx 50 alumnos, límites de memoria seguros y caché en disco.
     */
    public function massCourseBoletines(Course $course): Response
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if ($user->hasRole('docente')) {
            $teacher = $user->teacher;
            if (! $teacher || ! $this->teacherHasAccessToCourse($teacher, $course)) {
                abort(403, 'No tienes autorización para generar las boletas masivas de un grupo que no tienes asignado.');
            }
        } elseif (! $user->hasAnyRole(['admin', 'secretaria', 'superadmin'])) {
            abort(403, 'Acceso no autorizado.');
        }

        $totalStudents = Student::where('course_id', $course->id)
            ->where('status', 'activo')
            ->count();

        if ($totalStudents === 0) {
            abort(404, 'Este grupo no tiene alumnos activos para generar boletas.');
        }

        // Partición en bloques seguros de hasta 50 alumnos para no saturar memoria
        $chunkSize = 50;
        $part = max(1, (int) request()->query('part', 1));

        $students = Student::where('course_id', $course->id)
            ->where('status', 'activo')
            ->orderBy('last_name')
            ->skip(($part - 1) * $chunkSize)
            ->take($chunkSize)
            ->get();

        if ($students->isEmpty()) {
            abort(404, 'No hay más alumnos en este bloque.');
        }

        $suffix = $totalStudents > $chunkSize ? "_Parte_{$part}" : '';
        $filename = 'Boletas_Grupo_' . Str::slug($course->name . '_' . $course->section) . $suffix . '.pdf';

        // Caché en disco para servir instantáneamente si ya se generó recientemente
        $latestGradeChange = Grade::where('course_id', $course->id)->max('updated_at') ?? 'none';
        $cacheKey = "boletas_c{$course->id}_p{$part}_" . md5($latestGradeChange . '_' . $totalStudents);
        $cacheDir = storage_path('app/reports/cache');
        $cachePath = "{$cacheDir}/{$cacheKey}.pdf";

        if (file_exists($cachePath) && (time() - filemtime($cachePath) < 1800)) {
            $cachedContent = file_get_contents($cachePath);
            return response($cachedContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        $appSettings = Setting::current();
        $date = now();
        $studentsData = [];

        foreach ($students as $student) {
            $studentsData[] = $this->buildData($student);
        }

        $pdf = Pdf::loadView('reportcards.boletines_masivos', compact('course', 'studentsData', 'appSettings', 'date'))
            ->setPaper('letter', 'portrait');

        $pdfOutput = $pdf->output();

        if (! file_exists($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        @file_put_contents($cachePath, $pdfOutput);

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
