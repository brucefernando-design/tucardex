<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Notification;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    /**
     * El estudiante entrega una tarea.
     */
    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        $student = $request->user()->student;
        abort_unless($student, 403, 'Solo los estudiantes pueden entregar tareas.');

        // La tarea debe pertenecer al curso del estudiante
        abort_unless($assignment->course_id === $student->course_id, 403);

        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
            'file' => ['nullable', 'file', 'max:10240'], // hasta 10MB
        ]);

        $submission = AssignmentSubmission::firstOrNew([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
        ]);

        if ($request->hasFile('file')) {
            $disk = config('filesystems.default');
            if (!in_array($disk, ['r2', 's3', 'public', 'local'])) {
                $disk = 'public';
            }

            if ($submission->file) {
                try {
                    Storage::disk($disk)->delete($submission->file);
                } catch (\Exception $e) {
                    // ignora error si no existe
                }
            }

            // Almacenar organizado por colegio y tarea para escalabilidad masiva
            $folder = "submissions/escuela_{$student->school_id}/tarea_{$assignment->id}";
            $submission->file = $request->file('file')->store($folder, $disk);
        }

        $submission->comment = $data['comment'] ?? $submission->comment;
        $submission->status = 'entregado';
        $submission->submitted_at = now();
        $submission->save();

        return back()->with('success', 'Tarea entregada correctamente.');
    }

    /**
     * El docente/admin ve las entregas de una tarea.
     */
    public function index(Assignment $assignment): View
    {
        $assignment->load('course', 'subject');

        $students = Student::where('course_id', $assignment->course_id)
            ->where('status', 'activo')->orderBy('last_name')->get();

        $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->get()->keyBy('student_id');

        return view('submissions.index', compact('assignment', 'students', 'submissions'));
    }

    /**
     * El docente revisa/califica una entrega.
     */
    public function review(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:500'],
        ]);

        $submission->update([
            'score' => $data['score'] ?? null,
            'feedback' => $data['feedback'] ?? null,
            'status' => 'revisado',
            'reviewed_at' => now(),
        ]);

        // Notificar al estudiante
        Notification::notify(
            optional($submission->student)->user_id,
            'Tu tarea fue revisada',
            'task',
            'Revisaron tu entrega'.($submission->score !== null ? ': '.$submission->score : ''),
            route('dashboard')
        );

        return back()->with('success', 'Entrega revisada.');
    }
}
