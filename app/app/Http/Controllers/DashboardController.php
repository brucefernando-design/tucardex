<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return $this->superDashboard();
        }

        if ($user->hasRole('docente')) {
            return $this->teacherDashboard($user);
        }

        if ($user->hasRole('padre')) {
            return $this->parentDashboard($user);
        }

        if ($user->hasRole('estudiante')) {
            return $this->studentDashboard($user);
        }

        return $this->adminDashboard();
    }

    private function superDashboard(): View
    {
        $schools = School::withCount(['students', 'users'])->get();

        // Cálculo de MRR recurrente de la plataforma
        $totalMRR = 0;
        foreach ($schools as $sch) {
            if ($sch->status === 'activo') {
                $calc = $sch->calculateMonthlySubscription();
                $totalMRR += $calc['total'] ?? 0;
            }
        }

        // Volumen de cobro de colegiaturas recaudado este mes en todos los colegios
        $monthlyTuitionVolume = Payment::withoutGlobalScopes()
            ->where('status', 'pagado')
            ->whereMonth('paid_date', now()->month)
            ->whereYear('paid_date', now()->year)
            ->sum('amount');

        $stats = [
            'schools' => $schools->count(),
            'active' => $schools->where('status', 'activo')->count(),
            'students' => Student::withoutGlobalScopes()->count(),
            'users' => User::withoutGlobalScopes()->count(),
            'mrr' => $totalMRR,
            'tuition_volume' => (float) $monthlyTuitionVolume,
        ];

        $byPlan = School::selectRaw('plan, COUNT(*) as total')->groupBy('plan')->pluck('total', 'plan')->toArray();
        $recentSchools = School::withCount(['students', 'users'])->latest()->limit(10)->get();

        return view('dashboards.super', compact('stats', 'byPlan', 'recentSchools'));
    }

    private function adminDashboard(): View
    {
        $stats = [
            'students' => Student::where('status', 'activo')->count(),
            'teachers' => Teacher::count(),
            'courses' => Course::count(),
            'subjects' => Subject::count(),
            'today_absences' => Attendance::whereDate('date', today())->where('status', 'ausente')->count(),
        ];

        $todayAbsences = Attendance::with(['student', 'course'])
            ->whereDate('date', today())
            ->where('status', 'ausente')
            ->latest()
            ->limit(5)
            ->get();

        $overduePayments = Payment::with('student.course')
            ->where(function ($q) {
                $q->where('status', 'vencido')
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'pendiente')
                          ->whereNotNull('due_date')
                          ->where('due_date', '<', today());
                  });
            })
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $income = [
            'paid' => (float) Payment::where('status', 'pagado')->sum('amount'),
            'pending' => (float) Payment::whereIn('status', ['pendiente', 'vencido'])->sum('amount'),
            'overdue' => (float) Payment::where('status', 'vencido')->sum('amount'),
            'month_paid' => (float) Payment::where('status', 'pagado')
                ->whereMonth('paid_date', now()->month)
                ->whereYear('paid_date', now()->year)
                ->sum('amount'),
        ];

        $studentsByLevel = Course::query()
            ->selectRaw('level, COUNT(students.id) as total')
            ->leftJoin('students', 'students.course_id', '=', 'courses.id')
            ->groupBy('level')
            ->pluck('total', 'level')
            ->toArray();

        $dateFormat = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', paid_date) as ym"
            : "DATE_FORMAT(paid_date, '%Y-%m') as ym";

        $monthlyIncome = Payment::where('status', 'pagado')
            ->whereNotNull('paid_date')
            ->selectRaw("{$dateFormat}, SUM(amount) as total")
            ->groupBy('ym')
            ->orderByDesc('ym')
            ->limit(6)
            ->pluck('total', 'ym')
            ->reverse()
            ->toArray();

        $genderDistribution = [
            'M' => Student::where('gender', 'M')->count(),
            'F' => Student::where('gender', 'F')->count(),
        ];

        $recentStudents = Student::with('course')->latest()->limit(6)->get();
        $announcements = Announcement::with('author')->latest()->limit(5)->get();

        return view('dashboard', compact(
            'stats', 'income', 'studentsByLevel', 'monthlyIncome',
            'genderDistribution', 'recentStudents', 'announcements',
            'todayAbsences', 'overduePayments'
        ));
    }

    private function teacherDashboard($user): View
    {
        $teacher = $user->teacher;

        $tutoredCourses = $teacher
            ? Course::withCount('students')->where('tutor_id', $teacher->id)->get()
            : collect();

        $assignments = collect();
        $studentsCount = 0;
        if ($teacher) {
            $assignments = DB::table('course_subject')
                ->join('courses', 'courses.id', '=', 'course_subject.course_id')
                ->join('subjects', 'subjects.id', '=', 'course_subject.subject_id')
                ->where('course_subject.teacher_id', $teacher->id)
                ->select('courses.name as course', 'courses.section', 'subjects.name as subject', 'course_subject.hours_per_week')
                ->get();

            $studentsCount = Student::whereIn('course_id', $tutoredCourses->pluck('id'))->count();
        }

        $schedule = $teacher
            ? Schedule::with(['subject', 'course'])->where('teacher_id', $teacher->id)->orderBy('start_time')->get()->groupBy('day_of_week')
            : collect();

        $announcements = Announcement::with('author')->whereIn('audience', ['todos', 'docentes'])->latest()->limit(5)->get();
        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

        return view('dashboards.teacher', compact('teacher', 'tutoredCourses', 'assignments', 'studentsCount', 'schedule', 'announcements', 'days'));
    }

    /**
     * Dashboard exclusivo para el Alumno (rol estudiante).
     * Solo ve sus calificaciones, asistencia, tareas y avisos (NO pagos ni estado de cuenta).
     */
    private function studentDashboard($user): View
    {
        $student = $user->student;

        $grades = collect();
        $attendanceSummary = ['presente' => 0, 'ausente' => 0, 'tardanza' => 0, 'justificado' => 0];
        $average = null;

        if ($student) {
            $grades = Grade::with('subject')->where('student_id', $student->id)->get();
            $average = $grades->count() ? round($grades->avg('score'), 1) : null;

            foreach (Attendance::where('student_id', $student->id)->get() as $a) {
                $attendanceSummary[$a->status] = ($attendanceSummary[$a->status] ?? 0) + 1;
            }
        }

        $announcements = Announcement::with('author')->whereIn('audience', ['todos', 'estudiantes'])->latest()->limit(5)->get();

        $assignments = collect();
        $submissionsMap = collect();
        if ($student && $student->course_id) {
            $assignments = Assignment::with('subject')
                ->where('course_id', $student->course_id)
                ->where('status', 'activa')
                ->orderBy('due_date')
                ->limit(8)->get();

            $submissionsMap = \App\Models\AssignmentSubmission::where('student_id', $student->id)
                ->whereIn('assignment_id', $assignments->pluck('id'))
                ->get()->keyBy('assignment_id');
        }

        $bySubject = $grades->groupBy('subject_id')->map(function ($g) {
            return ['subject' => $g->first()->subject->name ?? '—', 'avg' => round($g->avg('score'), 1)];
        })->values();

        return view('dashboards.student', compact('student', 'grades', 'average', 'attendanceSummary', 'announcements', 'bySubject', 'assignments', 'submissionsMap'));
    }

    /**
     * Dashboard para Padres y Tutores (rol padre).
     * Puede tener 1 o varios hijos. Con selector si son varios.
     * Consulta académica completa + estado de cuenta y colegiaturas.
     */
    private function parentDashboard(User $user): View
    {
        $children = $user->children()->with('course')->get();

        if ($children->isEmpty()) {
            return view('dashboards.parent_empty');
        }

        // Selector de hijo
        $requestedId = request()->query('student');
        if ($requestedId) {
            $student = $children->firstWhere('id', (int) $requestedId);
            if (! $student) {
                abort(403, 'No tienes autorización para consultar el expediente de este estudiante.');
            }
            session(['parent_selected_student_id' => $student->id]);
        } elseif (session()->has('parent_selected_student_id')) {
            $sessId = session('parent_selected_student_id');
            $student = $children->firstWhere('id', (int) $sessId) ?? $children->first();
            session(['parent_selected_student_id' => $student->id]);
        } else {
            $student = $children->first();
            session(['parent_selected_student_id' => $student->id]);
        }

        $grades = collect();
        $attendanceSummary = ['presente' => 0, 'ausente' => 0, 'tardanza' => 0, 'justificado' => 0];
        $payments = collect();
        $average = null;

        if ($student) {
            $grades = Grade::with('subject')->where('student_id', $student->id)->get();
            $average = $grades->count() ? round($grades->avg('score'), 1) : null;

            foreach (Attendance::where('student_id', $student->id)->get() as $a) {
                $attendanceSummary[$a->status] = ($attendanceSummary[$a->status] ?? 0) + 1;
            }

            // Los padres SÍ pueden ver estado de cuenta / pagos del hijo
            $payments = Payment::where('student_id', $student->id)->latest()->get();
        }

        $announcements = Announcement::with('author')->whereIn('audience', ['todos', 'padres'])->latest()->limit(5)->get();

        $assignments = collect();
        $submissionsMap = collect();
        if ($student && $student->course_id) {
            $assignments = Assignment::with('subject')
                ->where('course_id', $student->course_id)
                ->where('status', 'activa')
                ->orderBy('due_date')
                ->limit(8)->get();

            $submissionsMap = \App\Models\AssignmentSubmission::where('student_id', $student->id)
                ->whereIn('assignment_id', $assignments->pluck('id'))
                ->get()->keyBy('assignment_id');
        }

        $bySubject = $grades->groupBy('subject_id')->map(function ($g) {
            return ['subject' => $g->first()->subject->name ?? '—', 'avg' => round($g->avg('score'), 1)];
        })->values();

        return view('dashboards.parent', compact(
            'user', 'children', 'student', 'grades', 'average',
            'attendanceSummary', 'payments', 'announcements',
            'bySubject', 'assignments', 'submissionsMap'
        ));
    }
}
