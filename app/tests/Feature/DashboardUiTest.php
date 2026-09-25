<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Role;
use App\Models\School;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardUiTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;
    protected User $adminUser;
    protected User $teacherUser;
    protected User $parentUser;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrador']);
        $docenteRole = Role::firstOrCreate(['slug' => 'docente'], ['name' => 'Docente']);
        $padreRole = Role::firstOrCreate(['slug' => 'padre'], ['name' => 'Padre']);

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
            'academic_year' => '2026',
        ]);

        // 3. Admin
        $this->adminUser = User::create([
            'name' => 'Director General',
            'email' => 'admin@colegio.test',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);

        // 4. Docente
        $teacher = Teacher::create([
            'school_id' => $this->school->id,
            'code' => 'DOC-001',
            'first_name' => 'Alejandra',
            'last_name' => 'Sánchez',
            'email' => 'docente@colegio.test',
            'status' => 'activo',
        ]);

        $this->teacherUser = User::create([
            'name' => 'Prof. Alejandra Sánchez',
            'email' => 'docente@colegio.test',
            'password' => Hash::make('password123'),
            'role_id' => $docenteRole->id,
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);
        $teacher->update(['user_id' => $this->teacherUser->id]);

        // 5. Curso
        $course = Course::create([
            'school_id' => $this->school->id,
            'name' => '1ro de Secundaria',
            'section' => 'A',
            'level' => 'Secundaria',
            'grade' => '1ro',
            'shift' => 'Mañana',
            'capacity' => 30,
            'tutor_id' => $teacher->id,
            'academic_year' => '2026',
            'status' => 'activo',
        ]);

        // 6. Alumno
        $this->student = Student::create([
            'school_id' => $this->school->id,
            'code' => 'EST-001',
            'first_name' => 'Sebastián',
            'last_name' => 'Sánchez',
            'course_id' => $course->id,
            'status' => 'activo',
        ]);

        // 7. Padre
        $this->parentUser = User::create([
            'name' => 'Roberto Sánchez',
            'email' => 'padre@colegio.test',
            'password' => Hash::make('password123'),
            'role_id' => $padreRole->id,
            'school_id' => $this->school->id,
            'is_active' => true,
        ]);
        $this->parentUser->children()->attach($this->student->id, ['relationship' => 'Padre']);

        // Inasistencia de hoy
        Attendance::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'date' => today()->toDateString(),
            'status' => 'ausente',
        ]);

        // Colegiatura pendiente / vencida
        Payment::create([
            'school_id' => $this->school->id,
            'student_id' => $this->student->id,
            'concept' => 'Colegiatura Septiembre',
            'amount' => 1500,
            'due_date' => today()->subDays(2),
            'status' => 'vencido',
        ]);
    }

    public function test_admin_dashboard_renders_cleanly_with_new_metrics(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Alumnos activos');
        $response->assertSee('Faltas de hoy');
        $response->assertSee('Por cobrar');
        $response->assertSee('Cobrado del mes');
        $response->assertSee('Hoy en el colegio');
        $response->assertSee('Pase de lista');
        $response->assertSee('Colegiaturas vencidas');
    }

    public function test_teacher_dashboard_renders_with_action_buttons_and_no_billing(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pasar Lista');
        $response->assertSee('Capturar Calificaciones');
        $response->assertSee('Boletas del Grupo');
        $response->assertDontSee('Colegiaturas vencidas');
        $response->assertDontSee('Por cobrar');
    }

    public function test_parent_dashboard_renders_with_summary_and_attendance_overview(): void
    {
        $response = $this->actingAs($this->parentUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Portal Familiar');
        $response->assertSee('Promedio general');
        $response->assertSee('Esta semana');
        $response->assertSee('Pagar colegiatura en línea');
    }
}
