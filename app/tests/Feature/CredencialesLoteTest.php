<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Role;
use App\Models\School;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CredencialesLoteTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected User $adminUser;
    protected Course $course;
    protected Student $student;

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
            'address' => 'Av. Principal #123',
            'phone' => '555-123-4567',
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
            'level' => 'Primaria',
            'status' => 'activo',
        ]);

        $this->student = Student::create([
            'school_id' => $this->school->id,
            'course_id' => $this->course->id,
            'first_name' => 'Ana',
            'last_name' => 'López Pérez',
            'code' => 'STU-001',
            'curp' => 'LOPE010101HDFR00',
            'guardian_name' => 'María Pérez',
            'guardian_phone' => '555-987-6543',
            'status' => 'activo',
        ]);
    }

    public function test_credenciales_view_renders_with_batch_dropdown_options(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('secretaria.credenciales', ['course_id' => $this->course->id]));

        $response->assertStatus(200);
        $response->assertSee('Imprimir Planilla Completa');
        $response->assertSee('Frente y Reverso Juntos');
        $response->assertSee('Impresión Dúplex');
    }

    public function test_descargar_credenciales_grupo_frente_reverso_generates_pdf(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('secretaria.credenciales.grupo', [
                'course' => $this->course->id,
                'tipo' => 'frente_reverso',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('Credenciales_', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Completa.pdf', $response->headers->get('content-disposition'));
    }

    public function test_descargar_credenciales_grupo_duplex_generates_pdf(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('secretaria.credenciales.grupo', [
                'course' => $this->course->id,
                'tipo' => 'duplex',
            ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('Credenciales_', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Duplex.pdf', $response->headers->get('content-disposition'));
    }
}
