<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use App\Services\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register', ['plans' => School::PLANS]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:150'],
            'plan' => ['required', 'in:basico,pro,institucional'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::min(6)],
        ], [
            'email.unique' => 'Ese correo ya está registrado.',
        ]);

        $user = DB::transaction(function () use ($data) {
            // 1) Crear el colegio (tenant)
            $school = School::create([
                'name' => $data['school_name'],
                'slug' => $this->uniqueSlug($data['school_name']),
                'plan' => $data['plan'],
                'status' => 'activo',
                'trial_ends_at' => now()->addDays(30),
                'email' => $data['email'],
            ]);

            // 2) Asegurar los roles globales
            $this->ensureRoles();
            $adminRole = Role::where('slug', 'admin')->first();

            // 3) Crear el usuario administrador del colegio
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => $adminRole->id,
                'school_id' => $school->id,
                'is_active' => true,
            ]);

            // 4) Configuración inicial del colegio
            Setting::create([
                'school_id' => $school->id,
                'school_name' => $school->name,
                'academic_year' => date('Y'),
                'active_period' => '1er Trimestre',
                'currency' => '$',
                'tuition_amount' => 250,
            ]);

            return $user;
        });

        // Activar el tenant e iniciar sesión
        app(Tenancy::class)->set($user->school_id);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', '¡Bienvenido! Tu colegio fue creado correctamente.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'colegio';
        $slug = $base;
        $i = 1;
        while (School::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    private function ensureRoles(): void
    {
        $roles = [
            ['name' => 'Administrador', 'slug' => 'admin', 'description' => 'Acceso total al sistema'],
            ['name' => 'Docente', 'slug' => 'docente', 'description' => 'Gestión de notas, asistencia y horarios'],
            ['name' => 'Secretaría', 'slug' => 'secretaria', 'description' => 'Matrículas, pagos y estudiantes'],
            ['name' => 'Estudiante / Padre', 'slug' => 'estudiante', 'description' => 'Consulta de notas y comunicados'],
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['slug' => $r['slug']], $r);
        }
    }
}
