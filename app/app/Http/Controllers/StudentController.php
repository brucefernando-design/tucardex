<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = Student::with('course')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('dni', 'like', "%{$search}%");
                });
            })
            ->when($request->course_id, fn ($q, $c) => $q->where('course_id', $c))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $courses = Course::orderBy('name')->get();

        return view('students.index', compact('students', 'courses'));
    }

    public function create(): View|RedirectResponse
    {
        $school = auth()->user()->school;
        if ($school && ! $school->canAddStudent()) {
            return redirect()->route('students.index')->with('error', 'Tu plan Básico admite 100 alumnos. Pasa a Profesional.');
        }
        $courses = Course::orderBy('name')->get();

        return view('students.create', compact('courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = auth()->user()->school;
        if ($school && ! $school->canAddStudent()) {
            return redirect()->route('students.index')->with('error', 'Tu plan Básico admite 100 alumnos. Pasa a Profesional.');
        }
        $data = $this->validateData($request);
        $data['code'] ??= 'EST-'.str_pad((string) (Student::max('id') + 1), 5, '0', STR_PAD_LEFT);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }

        // Crear o vincular cuenta de usuario (User) para que el alumno/tutor pueda acceder al sistema
        if (! empty($data['email'])) {
            $studentRole = Role::where('slug', 'estudiante')->first();
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => trim("{$data['first_name']} {$data['last_name']}"),
                    'password' => Hash::make(Str::random(32)), // Contraseña inicial: Matrícula
                    'role_id' => optional($studentRole)->id,
                    'school_id' => auth()->user()->school_id,
                    'phone' => $data['phone'] ?? $data['guardian_phone'] ?? null,
                    'is_active' => true,
                ]
            );
            $data['user_id'] = $user->id;
        }

        Student::create($data);

        return redirect()->route('students.index')
            ->with('success', 'Estudiante registrado correctamente con su cuenta de acceso.');
    }

    public function show(Student $student): View
    {
        $student->load(['course', 'payments', 'grades.subject', 'attendances', 'incidents.reporter']);

        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $courses = Course::orderBy('name')->get();

        return view('students.edit', compact('student', 'courses'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $this->validateData($request, $student);

        if ($request->hasFile('photo')) {
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }

        // Actualizar o crear la cuenta de usuario para acceso
        if (! empty($data['email'])) {
            if ($student->user) {
                $student->user->update([
                    'name' => trim("{$data['first_name']} {$data['last_name']}"),
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? $data['guardian_phone'] ?? $student->user->phone,
                ]);
            } else {
                $studentRole = Role::where('slug', 'estudiante')->first();
                $user = User::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => trim("{$data['first_name']} {$data['last_name']}"),
                        'password' => Hash::make(Str::random(32)),
                        'role_id' => optional($studentRole)->id,
                        'school_id' => auth()->user()->school_id,
                        'phone' => $data['phone'] ?? $data['guardian_phone'] ?? null,
                        'is_active' => true,
                    ]
                );
                $data['user_id'] = $user->id;
            }
        }

        $student->update($data);

        return redirect()->route('students.index')
            ->with('success', 'Estudiante actualizado correctamente.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Estudiante eliminado.');
    }

    /**
     * Exporta el listado de estudiantes (respeta filtros) a CSV o PDF.
     */
    public function export(Request $request)
    {
        $students = Student::with('course')
            ->when($request->course_id, fn ($q, $c) => $q->where('course_id', $c))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderBy('last_name')
            ->get();

        if ($request->get('format') === 'pdf') {
            return Pdf::loadView('exports.students_pdf', compact('students'))
                ->setPaper('letter', 'landscape')
                ->download('estudiantes_'.now()->format('Ymd').'.pdf');
        }

        return $this->streamCsv('estudiantes_'.now()->format('Ymd').'.csv',
            ['Código', 'Nombres', 'Apellidos', 'CI', 'Curso', 'Apoderado', 'Tel. apoderado', 'Estado'],
            $students->map(fn ($s) => [
                $s->code, $s->first_name, $s->last_name, $s->dni,
                optional($s->course)->name, $s->guardian_name, $s->guardian_phone, $s->status,
            ])
        );
    }

    /**
     * Muestra el formulario de importación masiva.
     */
    public function importForm(): View|RedirectResponse
    {
        $school = auth()->user()->school;
        if ($school && ! $school->canAddStudent()) {
            return redirect()->route('students.index')->with('error', 'Tu plan Básico admite 100 alumnos. Pasa a Profesional.');
        }
        $courses = Course::orderBy('name')->get();

        return view('students.import', compact('courses'));
    }

    /**
     * Descarga una plantilla CSV de ejemplo.
     */
    public function template(): StreamedResponse
    {
        return $this->streamCsv('plantilla_estudiantes.csv',
            ['nombres', 'apellidos', 'ci', 'fecha_nacimiento', 'genero', 'telefono', 'correo', 'apoderado', 'telefono_apoderado', 'curso'],
            [
                ['Juan', 'Pérez López', '12345678', '2012-05-10', 'M', '70011223', 'juan@ejemplo.com', 'María López', '70554433', '1ro de Secundaria'],
                ['Ana', 'Gutiérrez Soto', '87654321', '2013-08-22', 'F', '70099887', 'ana@ejemplo.com', 'Carlos Gutiérrez', '70112233', '1ro de Secundaria'],
            ]
        );
    }

    /**
     * Procesa el archivo CSV e inserta los estudiantes.
     */
    public function import(Request $request): RedirectResponse
    {
        $school = auth()->user()->school;
        if ($school && ! $school->canAddStudent()) {
            return back()->with('error', 'Tu plan Básico admite 100 alumnos. Pasa a Profesional.');
        }
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
            'course_id' => ['nullable', 'exists:courses,id'],
        ]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if (! $handle) {
            return back()->with('error', 'No se pudo leer el archivo.');
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);

            return back()->with('error', 'El archivo está vacío.');
        }

        // Normalizar encabezados (sin tildes/espacios, en minúscula)
        $headers = array_map(fn ($h) => $this->normalize($h), $headers);
        $courses = Course::all();
        $studentRole = Role::where('slug', 'estudiante')->first();

        $created = 0;
        $errors = 0;
        $next = (int) Student::max('id') + 1;

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // fila vacía
            }

            $data = array_combine($headers, array_pad($row, count($headers), null));

            $first = trim($data['nombres'] ?? $data['nombre'] ?? '');
            $last = trim($data['apellidos'] ?? $data['apellido'] ?? '');

            if ($first === '' || $last === '') {
                $errors++;
                continue;
            }

            // Determinar curso: columna del CSV o el seleccionado por defecto
            $courseId = $request->course_id;
            $courseName = trim($data['curso'] ?? '');
            if ($courseName !== '') {
                $match = $courses->first(fn ($c) => $this->normalize($c->name) === $this->normalize($courseName));
                $courseId = $match->id ?? $courseId;
            }

            $gender = strtoupper(trim($data['genero'] ?? ''));
            $gender = in_array($gender, ['M', 'F']) ? $gender : null;
            $code = 'EST-'.str_pad((string) $next++, 5, '0', STR_PAD_LEFT);
            $email = trim($data['correo'] ?? $data['email'] ?? '') ?: null;

            $userId = null;
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => "{$first} {$last}",
                        'password' => Hash::make($code),
                        'role_id' => optional($studentRole)->id,
                        'school_id' => auth()->user()->school_id,
                        'is_active' => true,
                    ]
                );
                $userId = $user->id;
            }

            Student::create([
                'user_id' => $userId,
                'code' => $code,
                'first_name' => $first,
                'last_name' => $last,
                'dni' => trim($data['ci'] ?? $data['carnet'] ?? '') ?: null,
                'birth_date' => $this->parseDate($data['fecha_nacimiento'] ?? null),
                'gender' => $gender,
                'phone' => trim($data['telefono'] ?? '') ?: null,
                'email' => $email,
                'guardian_name' => trim($data['apoderado'] ?? '') ?: null,
                'guardian_phone' => trim($data['telefono_apoderado'] ?? '') ?: null,
                'course_id' => $courseId,
                'enrollment_date' => now(),
                'status' => 'activo',
            ]);
            $created++;
        }

        fclose($handle);

        $msg = "Importación completada: $created estudiantes creados.";
        if ($errors) {
            $msg .= " $errors filas omitidas por datos incompletos.";
        }

        return redirect()->route('students.index')->with('success', $msg);
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);

        return preg_replace('/\s+/', ' ', $value);
    }

    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function streamCsv(string $filename, array $headers, $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel reconozca UTF-8
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validateData(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'code' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'dni' => ['nullable', 'string', 'max:30'],
            'curp' => ['nullable', 'string', 'size:18'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:M,F,Otro'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:120'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'enrollment_date' => ['nullable', 'date'],
            'status' => ['required', 'in:activo,inactivo,retirado'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'email.required' => 'El correo electrónico del estudiante o tutor es obligatorio para el acceso al sistema y notificaciones.',
            'email.email' => 'Debes ingresar un correo electrónico válido.',
        ]);
    }
}
