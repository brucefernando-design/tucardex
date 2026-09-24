<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::withCount('students')->orderBy('level')->orderBy('name')->get();
        $sourceId = $request->source_course_id;

        $students = collect();
        if ($sourceId) {
            $students = Student::where('course_id', $sourceId)
                ->where('status', 'activo')
                ->orderBy('last_name')->get();
        }

        $nextYear = (string) ((int) (Setting::current()->academic_year) + 1);

        return view('promotions.index', compact('courses', 'sourceId', 'students', 'nextYear'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_course_id' => ['required', 'exists:courses,id'],
            'action' => ['required', 'in:promover,egresar'],
            'target_course_id' => ['nullable', 'required_if:action,promover', 'different:source_course_id', 'exists:courses,id'],
            'academic_year' => ['required', 'string', 'max:10'],
            'students' => ['required', 'array', 'min:1'],
            'students.*' => ['exists:students,id'],
        ], [
            'students.required' => 'Selecciona al menos un estudiante.',
            'target_course_id.required_if' => 'Selecciona el curso destino.',
            'target_course_id.different' => 'El curso destino debe ser distinto al de origen.',
        ]);

        $count = 0;
        foreach ($data['students'] as $studentId) {
            $student = Student::find($studentId);
            if (! $student) {
                continue;
            }

            if ($data['action'] === 'promover') {
                $student->update(['course_id' => $data['target_course_id'], 'status' => 'activo']);
                Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $data['target_course_id'],
                    'academic_year' => $data['academic_year'],
                    'enrollment_date' => now(),
                    'status' => 'inscrito',
                    'observations' => 'Promovido desde curso anterior.',
                ]);
            } else { // egresar
                $student->update(['status' => 'inactivo']);
            }
            $count++;
        }

        $msg = $data['action'] === 'promover'
            ? "$count estudiante(s) promovido(s) correctamente."
            : "$count estudiante(s) marcados como egresados.";

        return redirect()->route('promotions.index')->with('success', $msg);
    }
}
