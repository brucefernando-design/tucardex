<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Course;
use App\Models\Role;
use App\Models\School;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\WhatsApp\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdmissionController extends Controller
{
    /**
     * Redirección pública inicial (/admisiones -> escuela principal o primera activa).
     */
    public function publicIndex(): RedirectResponse
    {
        $school = School::where('status', 'activo')->first();
        if (! $school) {
            abort(404, 'No hay instituciones escolares activas.');
        }

        return redirect()->route('admissions.public_form', $school->slug);
    }

    /**
     * Muestra la página pública de preinscripción y ficha de admisión para una escuela.
     */
    public function publicForm(string $slug): View
    {
        $school = School::where('slug', $slug)->firstOrFail();
        if ($school->status !== 'activo') {
            abort(403, 'El proceso de admisiones en línea para esta institución se encuentra temporalmente suspendido.');
        }

        $courses = Course::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('status', 'activo')
            ->orderBy('grade')
            ->orderBy('name')
            ->get();

        $setting = Setting::withoutGlobalScopes()->where('school_id', $school->id)->first() ?? new Setting();

        return view('admissions.public_form', compact('school', 'courses', 'setting'));
    }

    /**
     * Procesa la solicitud pública de preinscripción enviada por la familia.
     */
    public function publicSubmit(Request $request, string $slug): RedirectResponse
    {
        $school = School::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'curp' => ['nullable', 'string', 'max:18'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:M,F'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'grade_level' => ['nullable', 'string', 'max:100'],
            'previous_school' => ['nullable', 'string', 'max:150'],
            'guardian_name' => ['required', 'string', 'max:150'],
            'guardian_relationship' => ['required', 'string', 'max:50'],
            'guardian_phone' => ['required', 'string', 'max:30'],
            'guardian_email' => ['required', 'email', 'max:120'],
            'address' => ['nullable', 'string'],
            'medical_notes' => ['nullable', 'string'],
            'birth_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'curp_doc' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'address_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'previous_grades' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $folio = Admission::generateFolio($school->id);

        // Guardar archivos si fueron subidos
        $folder = "admissions/school_{$school->id}";
        $birthPath = $request->hasFile('birth_certificate') ? $request->file('birth_certificate')->store($folder, 'public') : null;
        $curpPath = $request->hasFile('curp_doc') ? $request->file('curp_doc')->store($folder, 'public') : null;
        $addressPath = $request->hasFile('address_proof') ? $request->file('address_proof')->store($folder, 'public') : null;
        $gradesPath = $request->hasFile('previous_grades') ? $request->file('previous_grades')->store($folder, 'public') : null;

        $admission = Admission::create([
            'school_id' => $school->id,
            'folio' => $folio,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'curp' => $validated['curp'] ? Str::upper($validated['curp']) : null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'course_id' => $validated['course_id'] ?? null,
            'grade_level' => $validated['grade_level'] ?? null,
            'previous_school' => $validated['previous_school'] ?? null,
            'guardian_name' => $validated['guardian_name'],
            'guardian_relationship' => $validated['guardian_relationship'],
            'guardian_phone' => $validated['guardian_phone'],
            'guardian_email' => $validated['guardian_email'],
            'address' => $validated['address'] ?? null,
            'medical_notes' => $validated['medical_notes'] ?? null,
            'birth_certificate_path' => $birthPath,
            'curp_path' => $curpPath,
            'address_proof_path' => $addressPath,
            'previous_grades_path' => $gradesPath,
            'status' => 'pendiente',
        ]);

        // Enviar WhatsApp de confirmación de recepción si WhatsApp está activo
        try {
            $setting = Setting::withoutGlobalScopes()->where('school_id', $school->id)->first();
            if ($setting && $setting->whatsapp_enabled) {
                $whatsapp = app(WhatsAppService::class);
                $status = $whatsapp->getStatus($school->id);
                if (($status['status'] ?? '') === 'connected') {
                    $colegioNombre = $setting->school_name ?: $school->name;
                    $msg = "Hola, estimado(a) *{$admission->guardian_name}* 👋\n\n" .
                        "Le confirmamos que la solicitud de preinscripción de su hijo(a) *{$admission->full_name}* en *{$colegioNombre}* fue recibida con éxito.\n\n" .
                        "📄 *Folio Oficial de Aspirante:* {$admission->folio}\n" .
                        "Consulte o descargue su comprobante aquí: " . route('admissions.public_success', ['slug' => $school->slug, 'folio' => $admission->folio]) . "\n\n" .
                        "El área de Control Escolar revisará su documentación a la brevedad. ¡Gracias por su confianza!";
                    
                    $whatsapp->sendMessage($admission->guardian_phone, $msg, $school->id);
                }
            }
        } catch (\Throwable $e) {
            logger()->warning('No se pudo enviar WhatsApp de admisión: ' . $e->getMessage());
        }

        return redirect()->route('admissions.public_success', ['slug' => $school->slug, 'folio' => $folio])
            ->with('success', '¡Tu solicitud de preinscripción ha sido recibida con éxito!');
    }

    /**
     * Pantalla pública de confirmación de registro de admisión.
     */
    public function publicSuccess(string $slug, string $folio): View
    {
        $school = School::where('slug', $slug)->firstOrFail();
        $admission = Admission::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('folio', $folio)
            ->firstOrFail();

        $setting = Setting::withoutGlobalScopes()->where('school_id', $school->id)->first() ?? new Setting();

        return view('admissions.public_success', compact('school', 'admission', 'setting'));
    }

    /**
     * Descarga de Ficha Oficial de Admisión en PDF.
     */
    public function publicPdf(string $slug, string $folio)
    {
        $school = School::where('slug', $slug)->firstOrFail();
        $admission = Admission::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('folio', $folio)
            ->firstOrFail();

        $setting = Setting::withoutGlobalScopes()->where('school_id', $school->id)->first() ?? new Setting();

        return Pdf::loadView('exports.admission_pdf', compact('school', 'admission', 'setting'))
            ->setPaper('letter', 'portrait')
            ->download('Ficha_Admision_' . $admission->folio . '.pdf');
    }

    // =========================================================================
    // MÓDULO ADMINISTRATIVO (SECRETARÍA Y CONTROL ESCOLAR)
    // =========================================================================

    public function index(Request $request): View
    {
        $schoolId = auth()->user()->school_id;

        $query = Admission::with('course')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('folio', 'like', "%{$s}%")
                    ->orWhere('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('guardian_name', 'like', "%{$s}%")
                    ->orWhere('curp', 'like', "%{$s}%");
            });
        }

        $admissions = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Admission::count(),
            'pendientes' => Admission::where('status', 'pendiente')->count(),
            'en_revision' => Admission::where('status', 'en_revision')->count(),
            'aceptadas' => Admission::where('status', 'aceptada')->count(),
            'matriculadas' => Admission::where('status', 'matriculada')->count(),
        ];

        $school = auth()->user()->school;

        return view('secretaria.admisiones.index', compact('admissions', 'stats', 'school'));
    }

    public function show(Admission $admission): View
    {
        $admission->load(['course', 'student']);
        $courses = Course::orderBy('name')->get();

        return view('secretaria.admisiones.show', compact('admission', 'courses'));
    }

    public function updateStatus(Request $request, Admission $admission): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:pendiente,en_revision,aceptada,rechazada'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $admission->update([
            'status' => $request->status,
            'internal_notes' => $request->internal_notes,
        ]);

        return back()->with('success', 'Estado de la solicitud de admisión actualizado.');
    }

    /**
     * Matricula en 1 clic al aspirante como alumno oficial del colegio.
     */
    public function matricular(Request $request, Admission $admission): RedirectResponse
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        if ($admission->status === 'matriculada' && $admission->student_id) {
            return back()->with('info', 'Este aspirante ya se encuentra matriculado como alumno.');
        }

        $school = auth()->user()->school;
        if ($school && ! $school->canAddStudent()) {
            return back()->with('error', 'Límite de alumnos alcanzado para tu suscripción actual.');
        }

        DB::transaction(function () use ($admission, $request, $school) {
            $studentCode = 'EST-' . str_pad((string) (Student::withoutGlobalScopes()->where('school_id', $school->id)->max('id') + 1), 5, '0', STR_PAD_LEFT);

            // 1. Crear al Alumno
            $student = Student::create([
                'school_id' => $school->id,
                'code' => $studentCode,
                'first_name' => $admission->first_name,
                'last_name' => $admission->last_name,
                'curp' => $admission->curp,
                'birth_date' => $admission->birth_date,
                'gender' => $admission->gender,
                'course_id' => $request->course_id,
                'guardian_name' => $admission->guardian_name,
                'guardian_phone' => $admission->guardian_phone,
                'email' => $admission->guardian_email,
                'address' => $admission->address,
                'enrollment_date' => now(),
                'status' => 'activo',
            ]);

            // 2. Crear o vincular cuenta del Padre / Tutor
            $padreRole = Role::firstOrCreate(['slug' => 'padre'], [
                'name' => 'Padre / Tutor',
                'description' => 'Consulta de expediente de hijos',
            ]);

            $guardianUser = User::withoutGlobalScopes()->where('email', $admission->guardian_email)->first();
            if (! $guardianUser) {
                $guardianUser = User::create([
                    'name' => $admission->guardian_name,
                    'email' => $admission->guardian_email,
                    'phone' => $admission->guardian_phone,
                    'password' => Hash::make(Str::random(16)),
                    'role_id' => $padreRole->id,
                    'school_id' => $school->id,
                    'is_active' => true,
                ]);
            }

            // Vincular en tabla pivote
            $student->guardians()->syncWithoutDetaching([
                $guardianUser->id => [
                    'relationship' => $admission->guardian_relationship ?: 'Tutor',
                    'is_primary' => true,
                    'school_id' => $school->id,
                ],
            ]);

            // 3. Marcar solicitud como matriculada
            $admission->update([
                'status' => 'matriculada',
                'student_id' => $student->id,
                'course_id' => $request->course_id,
            ]);

            // 4. WhatsApp felicitando a la familia por la matrícula oficial
            try {
                $setting = Setting::current();
                if ($setting && $setting->whatsapp_enabled) {
                    $whatsapp = app(WhatsAppService::class);
                    $colegioNombre = $setting->school_name ?: $school->name;
                    $msg = "🎉 *¡Felicidades, {$admission->guardian_name}!*\n\n" .
                        "La matrícula escolar de *{$student->full_name}* ha sido dada de alta exitosamente en *{$colegioNombre}*.\n\n" .
                        "🎓 *Matrícula Oficial:* {$student->code}\n" .
                        "🏫 *Grado:* {$student->course->name}\n\n" .
                        "A partir de este momento forman parte de nuestra comunidad educativa. ¡Bienvenidos!";
                    
                    $whatsapp->sendMessage($student->guardian_phone, $msg, $school->id);
                }
            } catch (\Throwable $e) {
                logger()->warning('WhatsApp matriculación fallo: ' . $e->getMessage());
            }
        });

        return redirect()->route('students.index')->with('success', "Aspirante {$admission->full_name} matriculado exitosamente como alumno oficial.");
    }
}
