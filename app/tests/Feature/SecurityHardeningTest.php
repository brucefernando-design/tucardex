<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Grade;
use App\Models\ImpersonationLog;
use App\Models\Payment;
use App\Models\Role;
use App\Models\School;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected User $superAdmin;
    protected User $teacherUser;
    protected Teacher $teacher;
    protected Course $assignedCourse;
    protected Course $unassignedCourse;
    protected Student $student;
    protected Payment $pendingPayment;
    protected Payment $paidPayment;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrador']);
        $docenteRole = Role::firstOrCreate(['slug' => 'docente'], ['name' => 'Docente']);
        $superadminRole = Role::firstOrCreate(['slug' => 'superadmin'], ['name' => 'SuperAdmin']);

        // 2. Escuela y Tenancy
        $this->school = School::create([
            'name' => 'Colegio San Martín',
            'slug' => 'colegio-san-martin',
            'status' => 'activo',
            'plan' => 'pro',
        ]);
        app(Tenancy::class)->set($this->school->id);

        Setting::create([
            'school_id' => $this->school->id,
            'school_name' => 'Colegio San Martín',
            'active_period' => '1er Trimestre',
        ]);

        // 3. SuperAdmin
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'bruce@allia2.com.mx',
            'password' => Hash::make('password123'),
            'role_id' => $superadminRole->id,
            'school_id' => null,
            'status' => 'activo',
        ]);

        // 4. Admin del colegio
        $adminUser = User::create([
            'name' => 'Director San Martín',
            'email' => 'admin@colegio.test',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'school_id' => $this->school->id,
            'status' => 'activo',
        ]);

        // 5. Docente
        $this->teacherUser = User::create([
            'name' => 'Profesor Asignado',
            'email' => 'docente@colegio.test',
            'password' => Hash::make('password123'),
            'role_id' => $docenteRole->id,
            'school_id' => $this->school->id,
            'status' => 'activo',
        ]);

        $this->teacher = Teacher::create([
            'school_id' => $this->school->id,
            'user_id' => $this->teacherUser->id,
            'code' => 'DOC-001',
            'first_name' => 'Profesor',
            'last_name' => 'Asignado',
            'email' => 'docente@colegio.test',
            'status' => 'activo',
        ]);

        // 6. Cursos
        $this->assignedCourse = Course::create([
            'school_id' => $this->school->id,
            'name' => '1ro Secundaria',
            'level' => 'Secundaria',
            'grade' => '1',
            'section' => 'A',
            'shift' => 'matutino',
            'tutor_id' => $this->teacher->id,
            'status' => 'activo',
        ]);

        $this->unassignedCourse = Course::create([
            'school_id' => $this->school->id,
            'name' => '3ro Secundaria',
            'level' => 'Secundaria',
            'grade' => '3',
            'section' => 'B',
            'shift' => 'matutino',
            'tutor_id' => null,
            'status' => 'activo',
        ]);

        // 7. Estudiante y Pagos
        $this->student = Student::create([
            'school_id' => $this->school->id,
            'course_id' => $this->assignedCourse->id,
            'code' => 'EST-001',
            'first_name' => 'Carlos',
            'last_name' => 'López',
            'status' => 'activo',
        ]);

        $this->pendingPayment = Payment::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'concept' => 'Colegiatura Octubre',
            'amount' => 3500.00,
            'status' => 'pendiente',
            'token' => Str::random(40),
        ]);

        $this->paidPayment = Payment::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'concept' => 'Inscripción Ciclo Escolar',
            'amount' => 4500.00,
            'status' => 'pagado',
            'paid_date' => now(),
            'token' => Str::random(40),
        ]);
    }

    /**
     * TEST 1: El checkout público solo es accesible vía token criptográfico opaco.
     */
    public function test_checkout_accessible_via_cryptographic_token(): void
    {
        $response = $this->get(route('parent.payments.checkout', $this->pendingPayment));
        $response->assertStatus(200);
        $response->assertViewIs('payments.checkout');
    }

    /**
     * TEST 2: Intentar adivinar IDs numéricos secuenciales devuelve 404 Not Found.
     */
    public function test_sequential_id_checkout_returns_404(): void
    {
        $response = $this->get('/pagos/1/checkout');
        $response->assertStatus(404);

        $response2 = $this->get('/pagos/999/checkout');
        $response2->assertStatus(404);
    }

    /**
     * TEST 3: El endpoint inseguro de simulación de pago fue eliminado por completo.
     */
    public function test_simulate_endpoint_is_removed_and_returns_404(): void
    {
        $response = $this->post('/pagos/1/simular');
        $response->assertStatus(404);
    }

    /**
     * TEST 4: Recibo público exige token válido y estatus pagado.
     */
    public function test_public_receipt_requires_paid_status_and_valid_token(): void
    {
        $response = $this->get(route('parent.payments.receipt', $this->paidPayment));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $responsePending = $this->get(route('parent.payments.receipt', $this->pendingPayment));
        $responsePending->assertStatus(403);
    }

    /**
     * TEST 5: La ruta interna de recibo por ID bloquea a usuarios no autenticados.
     */
    public function test_internal_receipt_blocks_unauthenticated_users(): void
    {
        $response = $this->get("/payments/{$this->paidPayment->id}/recibo");
        $response->assertRedirect('/login');
    }

    /**
     * TEST 6: El docente NO puede consultar actas de grupos ajenos.
     */
    public function test_teacher_cannot_access_unassigned_course_sheet(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('courses.acta', $this->unassignedCourse));
        $response->assertStatus(403);
    }

    /**
     * TEST 7: El docente NO puede generar boletas masivas de grupos ajenos.
     */
    public function test_teacher_cannot_access_unassigned_mass_report_cards(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('courses.boletas_masivas', $this->unassignedCourse));
        $response->assertStatus(403);
    }

    /**
     * TEST 8: La bitácora de auditoría registra el inicio y fin de la suplantación.
     */
    public function test_impersonation_creates_audit_log_and_tracks_exit(): void
    {
        $initialLogs = ImpersonationLog::count();

        // 1. Iniciar impersonación
        $response = $this->actingAs($this->superAdmin)->post(route('schools.impersonate', $this->school));
        $response->assertRedirect(route('dashboard'));

        $this->assertEquals($initialLogs + 1, ImpersonationLog::count());
        $log = ImpersonationLog::latest('id')->first();
        $this->assertEquals($this->superAdmin->id, $log->superadmin_id);
        $this->assertEquals($this->school->id, $log->school_id);
        $this->assertNull($log->ended_at);

        // 2. Salir de impersonación
        $leaveResponse = $this->withSession([
            'impersonator_id' => $this->superAdmin->id,
            'impersonator_school' => $this->school->id,
            'impersonation_log_id' => $log->id,
        ])->post(route('schools.leave_impersonation'));

        $log->refresh();
        $this->assertNotNull($log->ended_at, 'El campo ended_at debe ser registrado al salir');
    }

    /**
     * TEST 9: Borrar colegio purga todos sus datos y no deja registros huérfanos.
     */
    public function test_school_deletion_cascades_and_leaves_no_orphans(): void
    {
        // Crear colegio temporal para prueba de eliminación
        $tempSchool = School::create([
            'name' => 'Colegio Prueba Borrado',
            'slug' => 'colegio-prueba-borrado-' . time(),
            'status' => 'activo',
            'plan' => 'basico',
        ]);

        $student = Student::withoutGlobalScopes()->create([
            'school_id' => $tempSchool->id,
            'code' => 'EST-TEMP',
            'first_name' => 'Alumno',
            'last_name' => 'Temporal',
            'status' => 'activo',
        ]);

        $payment = Payment::withoutGlobalScopes()->create([
            'school_id' => $tempSchool->id,
            'student_id' => $student->id,
            'concept' => 'Prueba',
            'amount' => 100,
            'status' => 'pendiente',
        ]);

        // Ejecutar eliminación
        $response = $this->actingAs($this->superAdmin)->delete(route('schools.destroy', $tempSchool));
        $response->assertRedirect(route('schools.index'));

        // Verificar que no queden huérfanos
        $this->assertDatabaseMissing('schools', ['id' => $tempSchool->id]);
        $this->assertDatabaseMissing('students', ['school_id' => $tempSchool->id]);
        $this->assertDatabaseMissing('payments', ['school_id' => $tempSchool->id]);
    }

    /**
     * TEST 10: Webhook de pagos responde 200 ante payloads inválidos o no esperados sin romper PHP.
     */
    public function test_webhook_handles_malformed_payload_gracefully_without_500(): void
    {
        // 1. Payload vacío o mal formado
        $response = $this->postJson(route('webhooks.mercadopago'), []);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'ignored']);

        // 2. Payload de evento no soportado
        $response2 = $this->postJson(route('webhooks.mercadopago'), [
            'type' => 'merchant_order',
            'data' => ['id' => '12345'],
        ]);
        $response2->assertStatus(200);
        $response2->assertJson(['status' => 'ignored']);
    }

    /**
     * TEST 11: Acceso autorizado a boletas masivas compila correctamente el PDF sin agotar memoria.
     */
    public function test_authorized_mass_report_cards_compiles_successfully(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('courses.boletas_masivas', $this->assignedCourse));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
