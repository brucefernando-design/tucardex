<?php

namespace App\Http\Controllers;

use App\Mail\OficioInstitucionalMail;
use App\Models\Course;
use App\Models\DocumentFolio;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SecretariaController extends Controller
{
    /**
     * Dashboard / Centro de Control Escolar y Secretaría
     */
    public function index(Request $request)
    {
        $setting = Setting::current();
        $courses = Course::where('status', 'activo')->orderBy('grade')->orderBy('section')->get();

        $query = Student::with('course')->where('status', 'activo');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('curp', 'like', "%{$s}%")
                    ->orWhere('dni', 'like', "%{$s}%");
            });
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->paginate(15)->withQueryString();

        $stats = [
            'total_students' => Student::where('status', 'activo')->count(),
            'total_courses'  => Course::where('status', 'activo')->count(),
            'total_male'     => Student::where('status', 'activo')->where('gender', 'M')->count(),
            'total_female'   => Student::where('status', 'activo')->where('gender', 'F')->count(),
        ];

        return view('secretaria.index', compact('setting', 'courses', 'students', 'stats'));
    }

    /**
     * Módulo de Generación de Credenciales
     */
    public function credenciales(Request $request)
    {
        $setting = Setting::current();
        $courses = Course::where('status', 'activo')->withCount(['students' => function ($q) {
            $q->where('status', 'activo');
        }])->orderBy('grade')->orderBy('section')->get();

        $selectedCourse = null;
        $students = collect();

        if ($request->filled('course_id')) {
            $selectedCourse = Course::find($request->course_id);
            if ($selectedCourse) {
                $students = Student::where('course_id', $selectedCourse->id)
                    ->where('status', 'activo')
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get();
            }
        }

        return view('secretaria.credenciales', compact('setting', 'courses', 'selectedCourse', 'students'));
    }

    /**
     * Descargar Credencial Individual de Estudiante (Formato PVC con anverso y reverso)
     */
    public function descargarCredencial(Student $student, Request $request): Response
    {
        $student->load('course');
        $setting = Setting::current();

        $qrData = $this->generateQrCode("ALUMNO: {$student->full_name} | MATRICULA: {$student->code} | CURP: {$student->curp} | ESCUELA: {$setting->school_name} | CICLO: {$setting->academic_year}", 120);

        $pdf = Pdf::loadView('secretaria.pdf.credencial_single', compact('student', 'setting', 'qrData'))
            ->setPaper('letter', 'portrait');

        $cleanName = Str::slug($student->full_name, '_');
        return $pdf->download("Credencial_{$cleanName}.pdf");
    }

    /**
     * Descargar Lote de Credenciales por Grado/Grupo (8 credenciales por hoja carta)
     */
    public function descargarCredencialesGrupo(Course $course, Request $request): Response
    {
        $students = Student::where('course_id', $course->id)
            ->where('status', 'activo')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $setting = Setting::current();

        $studentsWithQr = $students->map(function ($student) use ($setting) {
            $student->qrData = $this->generateQrCode("ALUMNO: {$student->full_name} | MATRICULA: {$student->code} | CURP: {$student->curp} | ESCUELA: {$setting->school_name} | CICLO: {$setting->academic_year}", 90);
            return $student;
        });

        $pdf = Pdf::loadView('secretaria.pdf.credenciales_lote', [
            'students' => $studentsWithQr,
            'course' => $course,
            'setting' => $setting,
        ])->setPaper('letter', 'portrait');

        $cleanCourse = Str::slug($course->full_name, '_');
        return $pdf->download("Credenciales_Grupo_{$cleanCourse}.pdf");
    }

    /**
     * Centro de Emisión de Constancias y Certificaciones
     */
    public function constancias(Request $request)
    {
        $setting = Setting::current();
        $courses = Course::where('status', 'activo')->orderBy('grade')->orderBy('section')->get();

        $student = null;
        if ($request->filled('student_id')) {
            $student = Student::with(['course', 'grades.subject', 'payments', 'incidents'])->find($request->student_id);
        }

        return view('secretaria.constancias', compact('setting', 'courses', 'student'));
    }

    /**
     * Descarga Constancia de Estudios Oficial
     */
    public function descargarConstancia(Student $student, Request $request): Response
    {
        $student->load('course.tutor');
        $setting = Setting::current();

        $folio = 'CE-' . date('Y') . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
        $motivo = $request->get('motivo', 'los fines legales y administrativos que al interesado convengan.');
        $dirigidoA = $request->get('dirigido_a', 'A QUIEN CORRESPONDA');
        $incluirPromedio = $request->boolean('incluir_promedio', false);
        $promedio = null;

        if ($incluirPromedio) {
            $promedio = Grade::where('student_id', $student->id)->avg('score');
            $promedio = $promedio ? number_format($promedio, 2) : 'N/D';
        }

        $qrData = $this->generateQrCode("VALIDACION OFICIAL | FOLIO: {$folio} | CONSTANCIA DE ESTUDIOS | ALUMNO: {$student->full_name} | MATRICULA: {$student->code} | {$setting->school_name} | CCT: {$setting->cct}", 120);

        $pdf = Pdf::loadView('secretaria.pdf.constancia_estudios', compact(
            'student', 'setting', 'folio', 'motivo', 'dirigidoA', 'incluirPromedio', 'promedio', 'qrData'
        ))->setPaper('letter', 'portrait');

        $cleanName = Str::slug($student->full_name, '_');
        return $pdf->download("Constancia_Estudios_{$cleanName}.pdf");
    }

    /**
     * Descarga Carta de Buena Conducta
     */
    public function descargarBuenaConducta(Student $student, Request $request): Response
    {
        $student->load(['course', 'incidents']);
        $setting = Setting::current();

        $folio = 'CBC-' . date('Y') . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
        $dirigidoA = $request->get('dirigido_a', 'A QUIEN CORRESPONDA');
        $observaciones = $request->get('observaciones', 'Durante su permanencia en esta institución ha demostrado un comportamiento ejemplar, respetando las normas y valores de nuestra comunidad educativa.');

        $qrData = $this->generateQrCode("VALIDACION OFICIAL | FOLIO: {$folio} | CARTA DE BUENA CONDUCTA | ALUMNO: {$student->full_name} | MATRICULA: {$student->code} | {$setting->school_name}", 120);

        $pdf = Pdf::loadView('secretaria.pdf.carta_buena_conducta', compact(
            'student', 'setting', 'folio', 'dirigidoA', 'observaciones', 'qrData'
        ))->setPaper('letter', 'portrait');

        $cleanName = Str::slug($student->full_name, '_');
        return $pdf->download("Buena_Conducta_{$cleanName}.pdf");
    }

    /**
     * Descarga Constancia de No Adeudo
     */
    public function descargarNoAdeudo(Student $student, Request $request): Response
    {
        $student->load(['course', 'payments']);
        $setting = Setting::current();

        $folio = 'CNA-' . date('Y') . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
        $dirigidoA = $request->get('dirigido_a', 'A QUIEN CORRESPONDA');
        
        $pendientes = $student->payments->whereIn('status', ['pendiente', 'vencido'])->sum('amount');
        $tieneAdeudo = $pendientes > 0;

        $qrData = $this->generateQrCode("VALIDACION OFICIAL | FOLIO: {$folio} | CONSTANCIA NO ADEUDO | ALUMNO: {$student->full_name} | {$setting->school_name}", 120);

        $pdf = Pdf::loadView('secretaria.pdf.constancia_no_adeudo', compact(
            'student', 'setting', 'folio', 'dirigidoA', 'tieneAdeudo', 'pendientes', 'qrData'
        ))->setPaper('letter', 'portrait');

        $cleanName = Str::slug($student->full_name, '_');
        return $pdf->download("Constancia_No_Adeudo_{$cleanName}.pdf");
    }

    /**
     * Descarga Kárdex / Historial Académico Oficial
     */
    public function descargarKardex(Student $student, Request $request): Response
    {
        $student->load(['course.subjects', 'grades.subject']);
        $setting = Setting::current();

        $folio = 'KDX-' . date('Y') . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);

        $grades = Grade::where('student_id', $student->id)
            ->with('subject')
            ->get()
            ->groupBy('subject_id');

        $promedioGeneral = Grade::where('student_id', $student->id)->avg('score');

        $qrData = $this->generateQrCode("VALIDACION OFICIAL | FOLIO: {$folio} | KARDEX ACADEMICO | ALUMNO: {$student->full_name} | MATRICULA: {$student->code} | PROMEDIO: " . number_format($promedioGeneral, 2) . " | {$setting->school_name}", 120);

        $pdf = Pdf::loadView('secretaria.pdf.kardex_oficial', compact(
            'student', 'setting', 'folio', 'grades', 'promedioGeneral', 'qrData'
        ))->setPaper('letter', 'portrait');

        $cleanName = Str::slug($student->full_name, '_');
        return $pdf->download("Kardex_{$cleanName}.pdf");
    }

    /**
     * Descarga Ficha Oficial de Inscripción / Expediente del Alumno
     */
    public function descargarFichaMatricula(Student $student, Request $request): Response
    {
        $student->load(['course.tutor']);
        $setting = Setting::current();

        $folio = 'EXP-' . date('Y') . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);

        $qrData = $this->generateQrCode("FICHA DE INSCRIPCION | FOLIO: {$folio} | ALUMNO: {$student->full_name} | CURP: {$student->curp} | {$setting->school_name}", 120);

        $pdf = Pdf::loadView('secretaria.pdf.ficha_matricula', compact(
            'student', 'setting', 'folio', 'qrData'
        ))->setPaper('letter', 'portrait');

        $cleanName = Str::slug($student->full_name, '_');
        return $pdf->download("Ficha_Inscripcion_{$cleanName}.pdf");
    }

    /**
     * Generador de Citatorios y Justificantes Médicos / Oficiales
     */
    public function oficios(Request $request)
    {
        $setting = Setting::current();
        $courses = Course::where('status', 'activo')->withCount(['students' => function ($q) {
            $q->where('status', 'activo');
        }])->orderBy('grade')->orderBy('section')->get();

        $students = Student::where('status', 'activo')
            ->with(['course', 'user'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('secretaria.oficios', compact('setting', 'courses', 'students'));
    }

    /**
     * Descarga de Citatorio / Oficio Oficial en PDF
     */
    public function descargarOficio(Request $request): Response
    {
        $request->validate([
            'tipo' => 'required|in:citatorio,justificante,circular',
            'destinatario' => 'required|string',
            'asunto' => 'required|string',
            'cuerpo' => 'required|string',
        ]);

        $setting = Setting::current();
        $tipo = $request->tipo;
        $destinatario = $request->destinatario;
        $asunto = $request->asunto;
        $cuerpo = $request->cuerpo;
        $fechaCita = $request->fecha_cita;
        $horaCita = $request->hora_cita;
        $lugar = $request->lugar ?? 'Dirección del Plantel';
        $firmante = $request->firmante ?? ($setting->director ?? 'Dirección del Plantel');
        $cargo = $request->cargo ?? 'Director(a)';

        $schoolId = auth()->user()->school_id ?? optional($setting)->school_id ?? 1;
        $folio = DocumentFolio::nextFolio($schoolId, 'OFC');

        $qrData = $this->generateQrCode("OFICIO OFICIAL | FOLIO: {$folio} | ASUNTO: {$asunto} | {$setting->school_name}", 120);

        $pdf = Pdf::loadView('secretaria.pdf.oficio_citatorio', compact(
            'setting', 'tipo', 'destinatario', 'asunto', 'cuerpo', 'fechaCita', 'horaCita', 'lugar', 'firmante', 'cargo', 'folio', 'qrData'
        ))->setPaper('letter', 'portrait');

        $cleanTipo = Str::slug($tipo, '_');
        return $pdf->download("Oficio_{$cleanTipo}_{$folio}.pdf");
    }

    /**
     * Enviar Citatorio / Oficio Oficial por Correo Electrónico (Individual o Masivo por Grupo)
     */
    public function enviarOficio(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:citatorio,justificante,circular',
            'destinatario_tipo' => 'required|in:individual,grupo,manual',
            'asunto' => 'required|string',
            'cuerpo' => 'required|string',
        ]);

        $setting = Setting::current();
        $tipo = $request->tipo;
        $asunto = $request->asunto;
        $cuerpo = $request->cuerpo;
        $fechaCita = $request->fecha_cita;
        $horaCita = $request->hora_cita;
        $lugar = $request->lugar ?? 'Dirección del Plantel';
        $firmante = $request->firmante ?? ($setting->director ?? 'Dirección del Plantel');
        $cargo = $request->cargo ?? 'Director(a)';

        $recipients = collect();
        $destinatarioNombre = $request->destinatario ?? 'Comunidad Escolar';

        if ($request->destinatario_tipo === 'individual') {
            $student = Student::with(['user', 'guardians'])->find($request->student_id);
            if (! $student) {
                return back()->with('error', 'Por favor selecciona un estudiante válido.');
            }

            // Destinatario prioritario: correo de los tutores (guardians)
            $primaryGuardian = $student->guardians->sortByDesc(fn ($g) => $g->pivot->is_primary)->first();
            if ($primaryGuardian && $primaryGuardian->email) {
                $destinatarioNombre = "C. {$primaryGuardian->name} (Tutor de {$student->full_name})";
                $email = $primaryGuardian->email;
            } else {
                $destinatarioNombre = $student->guardian_name ? "C. {$student->guardian_name} (Tutor de {$student->full_name})" : $student->full_name;
                $email = $student->email ?? optional($student->user)->email;
            }

            // Si el alumno no tenía correo en BD pero la secretaria lo ingresó en el formulario rápido:
            // Se crea como rol PADRE (no como estudiante) y se envía reset link
            if (! $email && $request->filled('email_estudiante')) {
                $email = trim($request->email_estudiante);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $padreRole = Role::where('slug', 'padre')->first();
                    $guardianName = $student->guardian_name ?: "Tutor de {$student->full_name}";
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => $guardianName,
                            'password' => Hash::make(Str::random(32)),
                            'role_id' => optional($padreRole)->id,
                            'school_id' => auth()->user()->school_id,
                            'phone' => $student->guardian_phone,
                            'is_active' => true,
                        ]
                    );

                    $student->guardians()->syncWithoutDetaching([
                        $user->id => [
                            'school_id' => auth()->user()->school_id,
                            'relationship' => 'Tutor',
                            'is_primary' => true,
                        ],
                    ]);

                    try {
                        \Illuminate\Support\Facades\Password::broker()->sendResetLink(['email' => $user->email]);
                    } catch (\Throwable $e) {}

                    $destinatarioNombre = "C. {$user->name} (Tutor de {$student->full_name})";
                }
            }

            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $recipients->push($email);
            } else {
                return back()->with('error', "El alumno {$student->full_name} no tiene correo de tutor ni de estudiante registrado. Por favor ingresa el correo del tutor para continuar.");
            }
        } elseif ($request->destinatario_tipo === 'grupo') {
            $course = Course::with(['students.user', 'students.guardians'])->find($request->course_id);
            if (! $course) {
                return back()->with('error', 'Por favor selecciona un grupo escolar válido.');
            }

            $destinatarioNombre = "Padres de Familia y Alumnos de {$course->full_name}";

            foreach ($course->students as $st) {
                $guardianEmails = $st->guardians->pluck('email')->filter();
                if ($guardianEmails->isNotEmpty()) {
                    foreach ($guardianEmails as $ge) {
                        $recipients->push($ge);
                    }
                } else {
                    $email = $st->email ?? optional($st->user)->email;
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $recipients->push($email);
                    }
                }
            }

            $recipients = $recipients->unique();

            if ($recipients->isEmpty()) {
                return back()->with('error', "No se encontraron correos electrónicos válidos registrados en el grupo {$course->full_name}.");
            }
        } else {
            // Manual
            $destinatarioNombre = $request->destinatario_manual ?? 'Padre de Familia / Destinatario';
            $email = $request->email_manual;
            if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return back()->with('error', 'Por favor ingresa un correo electrónico válido.');
            }
            $recipients->push($email);
        }

        // Generar PDF Oficial con Folio y QR para adjuntarlo en el correo
        $schoolId = auth()->user()->school_id ?? optional($setting)->school_id ?? 1;
        $folio = DocumentFolio::nextFolio($schoolId, 'OFC');
        $qrData = $this->generateQrCode("OFICIO OFICIAL | FOLIO: {$folio} | ASUNTO: {$asunto} | {$setting->school_name}", 120);

        $pdf = Pdf::loadView('secretaria.pdf.oficio_citatorio', [
            'setting' => $setting,
            'tipo' => $tipo,
            'destinatario' => $destinatarioNombre,
            'asunto' => $asunto,
            'cuerpo' => $cuerpo,
            'fechaCita' => $fechaCita,
            'horaCita' => $horaCita,
            'lugar' => $lugar,
            'firmante' => $firmante,
            'cargo' => $cargo,
            'folio' => $folio,
            'qrData' => $qrData,
        ])->setPaper('letter', 'portrait');

        $pdfContent = $pdf->output();
        $pdfFilename = Str::slug("Oficio_{$tipo}_{$folio}", '_') . '.pdf';

        try {
            $fromAddress = config('mail.from.address') ?: 'notificaciones@tucardex.allia2.com.mx';

            if ($recipients->count() === 1) {
                Mail::to($recipients->first())->queue(new OficioInstitucionalMail(
                    $tipo, $asunto, $cuerpo, $destinatarioNombre, $fechaCita, $horaCita, $lugar, $firmante, $cargo, $setting->school_name, $pdfContent, $pdfFilename
                ));
            } else {
                // Envío grupal en BCC para proteger privacidad de correos
                Mail::to($fromAddress)->bcc($recipients->all())->queue(new OficioInstitucionalMail(
                    $tipo, $asunto, $cuerpo, $destinatarioNombre, $fechaCita, $horaCita, $lugar, $firmante, $cargo, $setting->school_name, $pdfContent, $pdfFilename
                ));
            }

            $count = $recipients->count();
            return back()->with('success', "¡Oficio enviado exitosamente con PDF adjunto a {$count} destinatario(s)!");
        } catch (\Throwable $e) {
            return back()->with('warning', "El PDF se generó correctamente, pero el servicio de correo reportó: " . $e->getMessage());
        }
    }

    /**
     * Helper para generar QR Code en Data URI PNG
     */
    private function generateQrCode(string $content, int $size = 120): ?string
    {
        if (! class_exists(QrCode::class) || ! class_exists(PngWriter::class)) {
            return null;
        }

        try {
            $writer = new PngWriter();
            $qr = QrCode::create($content)
                ->setSize($size)
                ->setMargin(4);

            return $writer->write($qr)->getDataUri();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
