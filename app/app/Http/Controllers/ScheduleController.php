<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public array $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    public function index(Request $request): View
    {
        $courses = Course::orderBy('name')->get();
        $courseId = $request->course_id ?: optional($courses->first())->id;

        $schedules = Schedule::with(['subject', 'teacher'])
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::orderBy('last_name')->get();
        $days = $this->days;

        return view('schedules.index', compact('courses', 'courseId', 'schedules', 'subjects', 'teachers', 'days'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
            'day_of_week' => ['required', 'string'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'classroom' => ['nullable', 'string', 'max:50'],
        ]);

        Schedule::create($data);

        return redirect()->route('schedules.index', ['course_id' => $request->course_id])
            ->with('success', 'Horario agregado.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $courseId = $schedule->course_id;
        $schedule->delete();

        return redirect()->route('schedules.index', ['course_id' => $courseId])
            ->with('success', 'Horario eliminado.');
    }
}
