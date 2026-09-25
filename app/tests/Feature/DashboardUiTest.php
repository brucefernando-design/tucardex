<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardUiTest extends TestCase
{
    public function test_admin_dashboard_renders_cleanly_with_new_metrics(): void
    {
        $school = School::first() ?? School::create([
            'name' => 'Colegio Test',
            'slug' => 'colegio-test',
            'plan' => 'pro',
            'status' => 'activo',
        ]);
        app(Tenancy::class)->set($school->id);

        $adminRole = Role::where('slug', 'admin')->first();
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@colegio.test'],
            ['name' => 'Admin Test', 'password' => bcrypt('password'), 'role_id' => $adminRole->id, 'school_id' => $school->id]
        );

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Alumnos activos');
        $response->assertSee('Faltas de hoy');
        $response->assertSee('Por cobrar');
        $response->assertSee('Cobrado del mes');
        $response->assertSee('Hoy en el colegio');
        $response->assertSee('Pase de lista');
    }

    public function test_teacher_dashboard_renders_with_action_buttons_and_no_billing(): void
    {
        $school = School::first();
        app(Tenancy::class)->set($school->id);

        $teacherRole = Role::where('slug', 'docente')->first();
        $teacherUser = User::firstOrCreate(
            ['email' => 'docente_test@colegio.test'],
            ['name' => 'Profesor Test', 'password' => bcrypt('password'), 'role_id' => $teacherRole->id, 'school_id' => $school->id]
        );

        $response = $this->actingAs($teacherUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pasar Lista');
        $response->assertSee('Capturar Calificaciones');
        $response->assertSee('Boletas del Grupo');
        $response->assertDontSee('Colegiaturas vencidas');
        $response->assertDontSee('Por cobrar');
    }

    public function test_parent_dashboard_renders_with_summary_and_attendance_overview(): void
    {
        $school = School::first();
        app(Tenancy::class)->set($school->id);

        $parentRole = Role::where('slug', 'padre')->first();
        $parentUser = User::where('role_id', $parentRole->id)->first();

        if ($parentUser && $parentUser->children()->count() > 0) {
            $response = $this->actingAs($parentUser)->get(route('dashboard'));

            $response->assertStatus(200);
            $response->assertSee('Portal Familiar');
            $response->assertSee('Promedio general');
            $response->assertSee('Esta semana');
        }
    }
}
