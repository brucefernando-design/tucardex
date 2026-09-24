<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $assignments = Assignment::with(['course', 'subject', 'teacher'])
            ->when($request->course_id, fn ($q, $c) => $q->where('course_id', $c))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('due_date', 'desc')
            ->paginate(12)
            ->withQueryString();

        $courses = Course::orderBy('name')->get();

        return view('assignments.index', compact('assignments', 'courses'));
    }

    public function create(): View
    {
        return view('assignments.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;
        $data['assigned_date'] ??= now()->toDateString();

        // Si el usuario es docente, asociar su registro de docente
        $data['teacher_id'] = $data['teacher_id'] ?? optional($request->user()->teacher)->id;

        Assignment::create($data);

        return redirect()->route('assignments.index')->with('success', 'Tarea publicada correctamente.');
    }

    public function edit(Assignment $assignment): View
    {
        return view('assignments.edit', array_merge($this->formData(), compact('assignment')));
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $assignment->update($this->validateData($request));

        return redirect()->route('assignments.index')->with('success', 'Tarea actualizada.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $assignment->delete();

        return redirect()->route('assignments.index')->with('success', 'Tarea eliminada.');
    }

    private function formData(): array
    {
        return [
            'courses' => Course::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => Teacher::orderBy('last_name')->get(),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'course_id' => ['required', 'exists:courses,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'assigned_date' => ['nullable', 'date'],
            'due_date' => ['required', 'date'],
            'status' => ['required', 'in:activa,cerrada'],
        ]);
    }
}
