<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseSubjectController extends Controller
{
    public function index(Course $course): View
    {
        $course->load(['subjects' => fn ($q) => $q->orderBy('name')]);

        // Materias todavía no asignadas a este curso
        $assignedIds = $course->subjects->pluck('id');
        $availableSubjects = Subject::whereNotIn('id', $assignedIds)->orderBy('name')->get();
        $teachers = Teacher::where('status', 'activo')->orderBy('last_name')->get();
        $teachersById = Teacher::all()->keyBy('id');

        return view('courses.academic', compact('course', 'availableSubjects', 'teachers', 'teachersById'));
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'hours_per_week' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        if ($course->subjects()->where('subject_id', $data['subject_id'])->exists()) {
            return back()->with('error', 'Esa materia ya está asignada a este curso.');
        }

        $course->subjects()->attach($data['subject_id'], [
            'teacher_id' => $data['teacher_id'] ?? null,
            'hours_per_week' => $data['hours_per_week'],
        ]);

        return back()->with('success', 'Materia asignada al curso.');
    }

    public function update(Request $request, Course $course, Subject $subject): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'hours_per_week' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $course->subjects()->updateExistingPivot($subject->id, [
            'teacher_id' => $data['teacher_id'] ?? null,
            'hours_per_week' => $data['hours_per_week'],
        ]);

        return back()->with('success', 'Asignación actualizada.');
    }

    public function destroy(Course $course, Subject $subject): RedirectResponse
    {
        $course->subjects()->detach($subject->id);

        return back()->with('success', 'Materia quitada del curso.');
    }
}
