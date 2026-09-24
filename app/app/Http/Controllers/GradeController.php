<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Grade;
use App\Models\Notification;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $grades = Grade::with(['student', 'subject', 'course'])
            ->when($request->course_id, fn ($q, $c) => $q->where('course_id', $c))
            ->when($request->subject_id, fn ($q, $s) => $q->where('subject_id', $s))
            ->when($request->period, fn ($q, $p) => $q->where('period', $p))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $courses = Course::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $students = Student::orderBy('last_name')->get();

        return view('grades.index', compact('grades', 'courses', 'subjects', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'period' => ['required', 'string'],
            'type' => ['required', 'string'],
            'score' => ['required', 'numeric', 'min:5', 'max:10'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $grade = Grade::create($data);

        // Notificar al estudiante (si tiene cuenta de usuario)
        $student = Student::find($data['student_id']);
        if ($student && $student->user_id) {
            $subjectName = optional(Subject::find($data['subject_id']))->name;
            Notification::notify(
                $student->user_id,
                'Nueva calificación registrada',
                'grade',
                "Se registró tu nota de {$subjectName}: {$data['score']} ({$data['period']})",
                route('dashboard')
            );
        }

        return redirect()->route('grades.index')->with('success', 'Calificación registrada.');
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        $grade->delete();

        return redirect()->route('grades.index')->with('success', 'Calificación eliminada.');
    }

    /**
     * Planilla de notas masiva: registra/edita las notas de todo un curso
     * para una materia, periodo y tipo determinados.
     */
    public function batch(Request $request): View
    {
        $courses = Course::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $periods = ['1er Trimestre', '2do Trimestre', '3er Trimestre', 'Final'];
        $types = ['examen', 'practica', 'tarea', 'proyecto', 'actitudinal'];

        $courseId = $request->course_id;
        $subjectId = $request->subject_id;
        $period = $request->period ?: (\App\Models\Setting::current()->active_period);
        $type = $request->type ?: 'examen';

        $students = collect();
        $existing = collect();

        if ($courseId && $subjectId) {
            $students = Student::where('course_id', $courseId)
                ->where('status', 'activo')
                ->orderBy('last_name')->get();

            $existing = Grade::where('course_id', $courseId)
                ->where('subject_id', $subjectId)
                ->where('period', $period)
                ->where('type', $type)
                ->get()
                ->keyBy('student_id');
        }

        return view('grades.batch', compact(
            'courses', 'subjects', 'periods', 'types',
            'courseId', 'subjectId', 'period', 'type', 'students', 'existing'
        ));
    }

    public function batchStore(Request $request): RedirectResponse
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'period' => ['required', 'string'],
            'type' => ['required', 'string'],
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:5', 'max:10'],
        ]);

        $saved = 0;
        foreach ($request->scores as $studentId => $score) {
            if ($score === null || $score === '') {
                continue;
            }

            Grade::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $request->subject_id,
                    'course_id' => $request->course_id,
                    'period' => $request->period,
                    'type' => $request->type,
                ],
                ['score' => $score]
            );
            $saved++;
        }

        return redirect()->route('grades.batch', $request->only('course_id', 'subject_id', 'period', 'type'))
            ->with('success', "Se guardaron $saved calificaciones.");
    }
}
