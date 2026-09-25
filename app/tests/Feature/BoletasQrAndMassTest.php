<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DocumentVerification;
use App\Models\Grade;
use App\Models\Role;
use App\Models\School;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\QrCodeService;
use App\Services\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BoletasQrAndMassTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected User $adminUser;
    protected Course $course;
    protected Student $student;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrador']);

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
            'academic_year' => '2026',
            'address' => 'Av. Universidad 456',
            'phone' => '555-900-1122',
            'director' => 'Lic. Roberto Gómez',
        ]);

        $this->adminUser = User::create([
            'name' => 'Director General',
            'email' => 'admin@colegio.test',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'school_id' => $this->school->id,
        ]);

        $this->course = Course::create([
            'school_id' => $this->school->id,
            'name' => '1°',
            'section' => 'A',
            'grade' => '1',
            'level' => 'Secundaria',
            'status' => 'activo',
        ]);

        $this->student = Student::create([
            'school_id' => $this->school->id,
            'course_id' => $this->course->id,
            'first_name' => 'Roberto',
            'last_name' => 'Cárdenas Reyes',
            'code' => 'EST-00007',
            'curp' => '10675922',
            'guardian_name' => 'Carlos Cárdenas Díaz',
            'guardian_phone' => '76537056',
            'status' => 'activo',
        ]);

        $this->subject = Subject::create([
            'school_id' => $this->school->id,
            'name' => 'Matemáticas',
            'code' => 'MAT-1',
        ]);

        Grade::create([
            'school_id' => $this->school->id,
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'period' => '1er Trimestre',
            'score' => 9.5,
        ]);
    }

    public function test_qr_code_service_generates_valid_png_data_uri(): void
    {
        $uri = QrCodeService::generateDataUri('TEST QR VALIDATION', 100);

        $this->assertNotNull($uri);
        $this->assertStringStartsWith('data:image/png;base64,', $uri);
        $this->assertGreaterThan(100, strlen($uri));
    }

    public function test_individual_boleta_includes_qr_code_and_creates_verification(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('students.boletin', $this->student));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $verif = DocumentVerification::where('student_id', $this->student->id)
            ->where('doc_type', 'boleta_calificaciones')
            ->first();

        $this->assertNotNull($verif);
        $this->assertEquals(24, strlen($verif->token));
    }

    public function test_constancia_estudios_creates_verification_and_qr(): void
    {
        // 1. Desde perfil del estudiante (students.constancia)
        $res1 = $this->actingAs($this->adminUser)
            ->get(route('students.constancia', $this->student));
        $res1->assertStatus(200);
        $res1->assertHeader('content-type', 'application/pdf');

        // 2. Desde secretaría (secretaria.constancia.descargar)
        $res2 = $this->actingAs($this->adminUser)
            ->get(route('secretaria.constancia.descargar', $this->student));
        $res2->assertStatus(200);
        $res2->assertHeader('content-type', 'application/pdf');

        $verif = DocumentVerification::where('student_id', $this->student->id)
            ->where('doc_type', 'constancia_estudios')
            ->first();

        $this->assertNotNull($verif);

        // 3. Probar la página pública de verificación al escanear el QR
        $publicPage = $this->get(route('documentos.verificar', ['token' => $verif->token]));
        $publicPage->assertStatus(200);
        $publicPage->assertSee('Documento Oficial Auténtico');
        $publicPage->assertSee('Roberto Cárdenas Reyes');
        $publicPage->assertSee('EST-00007');
        $publicPage->assertSee('Colegio San Martín');
        // No debe mostrar el teléfono privado del tutor
        $publicPage->assertDontSee('76537056');
    }

    public function test_invalid_verification_token_shows_not_recognized_message(): void
    {
        $response = $this->get(route('documentos.verificar', ['token' => 'token-inexistente-999999']));
        $response->assertStatus(200);
        $response->assertSee('Código de Verificación No Reconocido');
    }

    public function test_mass_boletas_generates_pdf_for_course(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('courses.boletas_masivas', $this->course));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('Boletas_Grupo_', $response->headers->get('content-disposition'));
    }

    public function test_credenciales_pdf_includes_generated_qr(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('secretaria.credenciales.grupo', [
                'course' => $this->course->id,
                'tipo' => 'frente_reverso',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_students_index_renders_mass_boletas_button_and_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('students.index'));

        $response->assertStatus(200);
        $response->assertSee('Boletas Masivas');
        $response->assertSee(route('courses.boletas_masivas', $this->course));

        $filteredResponse = $this->actingAs($this->adminUser)
            ->get(route('students.index', ['course_id' => $this->course->id]));

        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee('Grupo Filtrado:');
        $filteredResponse->assertSee('Imprimir Boletas de');
    }

    public function test_constancia_estudios_generates_exactly_single_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('secretaria.constancia.descargar', $this->student));

        $response->assertStatus(200);
        $content = $response->getContent();

        // En especificación PDF, cada página se declara como un diccionario /Type /Page
        preg_match_all('/\/Type\s*\/Page\b/', $content, $matches);
        $pageCount = count($matches[0]);

        $this->assertEquals(1, $pageCount, "La constancia de estudios debe caber exactamente en 1 sola hoja.");
    }
}
