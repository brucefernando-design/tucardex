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

        if ($user->hasRole('estudiante')) {
            return $this->studentDashboard($user);
        }

        return $this->adminDashboard();
    }

    private function superDashboard(): View
    {
        $stats = [
            'schools' => School::count(),
            'active' => School::where('status', 'activo')->count(),
            'students' => Student::count(),
            'users' => User::count(),
        ];

        $byPlan = School::selectRaw('plan, COUNT(*) as total')->groupBy('plan')->pluck('total', 'plan')->toArray();

        $recentSchools = School::withCount('students')->latest()->limit(8)->get();

        return view('dashboards.super', compact('stats', 'byPlan', 'recentSchools'));
    }

    private function adminDashboard(): View
    {
        $stats = [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'courses' => Course::count(),
            'subjects' => Subject::count(),
        ];

        $income = [
            'paid' => (float) Payment::where('status', 'pagado')->sum('amount'),
            'pending' => (float) Payment::where('status', 'pendiente')->sum('amount'),
            'overdue' => (float) Payment::where('status', 'vencido')->sum('amount'),
        ];

        $studentsByLevel = Course::query()
            ->selectRaw('level, COUNT(students.id) as total')
            ->leftJoin('students', 'students.course_id', '=', 'courses.id')
            ->groupBy('level')
            ->pluck('total', 'level')
            ->toArray();

        // Últimos 6 meses con ingresos (más recientes), en orden cronológico ascendente
        $monthlyIncome = Payment::where('status', 'pagado')
            ->whereNotNull('paid_date')
            ->selectRaw("DATE_FORMAT(paid_date, '%Y-%m') as ym, SUM(amount) as total")
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
            'genderDistribution', 'recentStudents', 'announcements'
        ));
    }

    private function teacherDashboard($user): View
    {
        $teacher = $user->teacher;

        $tutoredCourses = $teacher
            ? Course::withCount('students')->where('tutor_id', $teacher->id)->get()
            : collect();

        // Materias que dicta (desde la carga académica)
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

    private function studentDashboard($user): View
    {
        $student = $user->student;

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

            $payments = Payment::where('student_id', $student->id)->latest()->get();
        }

        $announcements = Announcement::with('author')->whereIn('audience', ['todos', 'estudiantes', 'padres'])->latest()->limit(5)->get();

        // Tareas pendientes del curso del estudiante
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

        // Promedio por materia para gráfico
        $bySubject = $grades->groupBy('subject_id')->map(function ($g) {
            return ['subject' => $g->first()->subject->name ?? '—', 'avg' => round($g->avg('score'), 1)];
        })->values();

        return view('dashboards.student', compact('student', 'grades', 'average', 'attendanceSummary', 'payments', 'announcements', 'bySubject', 'assignments', 'submissionsMap'));
    }
}
