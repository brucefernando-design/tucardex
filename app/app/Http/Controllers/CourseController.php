<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::withCount('students')->with('tutor')
            ->when($request->level, fn ($q, $l) => $q->where('level', $l))
            ->orderBy('level')->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('courses.index', compact('courses'));
    }

    public function create(): View
    {
        $teachers = Teacher::where('status', 'activo')->orderBy('last_name')->get();

        return view('courses.create', compact('teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        Course::create($this->validateData($request));

        return redirect()->route('courses.index')
            ->with('success', 'Curso creado correctamente.');
    }

    public function show(Course $course): View
    {
        $course->load(['tutor', 'students', 'subjects']);

        return view('courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        $teachers = Teacher::where('status', 'activo')->orderBy('last_name')->get();

        return view('courses.edit', compact('course', 'teachers'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $course->update($this->validateData($request));

        return redirect()->route('courses.index')
            ->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', 'Curso eliminado.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'level' => ['required', 'in:Inicial,Primaria,Secundaria'],
            'grade' => ['nullable', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:10'],
            'shift' => ['required', 'in:Mañana,Tarde,Noche'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'tutor_id' => ['nullable', 'exists:teachers,id'],
            'academic_year' => ['required', 'string', 'max:10'],
            'status' => ['required', 'in:activo,inactivo'],
        ]);
    }
}
