<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(Request $request): View
    {
        $incidents = Incident::with(['student', 'reporter'])
            ->when($request->student_id, fn ($q, $s) => $q->where('student_id', $s))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->latest('date')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'merito' => Incident::where('type', 'merito')->count(),
            'demerito' => Incident::where('type', 'demerito')->count(),
            'observacion' => Incident::where('type', 'observacion')->count(),
        ];

        $students = Student::where('status', 'activo')->orderBy('last_name')->get();

        return view('incidents.index', compact('incidents', 'summary', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'date' => ['required', 'date'],
            'type' => ['required', 'in:merito,demerito,observacion'],
            'category' => ['nullable', 'string', 'max:80'],
            'description' => ['required', 'string'],
            'points' => ['nullable', 'integer', 'min:-50', 'max:50'],
        ]);

        $data['points'] = $data['points'] ?? ($data['type'] === 'merito' ? 5 : ($data['type'] === 'demerito' ? -5 : 0));
        $data['reported_by'] = $request->user()->id;

        Incident::create($data);

        return redirect()->route('incidents.index')->with('success', 'Registro de conducta guardado.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $incident->delete();

        return redirect()->route('incidents.index')->with('success', 'Registro eliminado.');
    }
}
