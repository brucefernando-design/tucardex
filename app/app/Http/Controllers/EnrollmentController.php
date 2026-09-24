<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $enrollments = Enrollment::with(['student', 'course'])
            ->when($request->course_id, fn ($q, $c) => $q->where('course_id', $c))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $courses = Course::orderBy('name')->get();
        $students = Student::orderBy('last_name')->get();

        return view('enrollments.index', compact('enrollments', 'courses', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'academic_year' => ['required', 'string', 'max:10'],
            'enrollment_date' => ['required', 'date'],
            'status' => ['required', 'in:inscrito,retirado,trasladado'],
            'observations' => ['nullable', 'string'],
        ]);

        Enrollment::create($data);
        Student::where('id', $data['student_id'])->update(['course_id' => $data['course_id']]);

        return redirect()->route('enrollments.index')
            ->with('success', 'Matrícula registrada correctamente.');
    }

    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        $enrollment->delete();

        return redirect()->route('enrollments.index')->with('success', 'Matrícula eliminada.');
    }
}
