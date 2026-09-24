<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Role;
use App\Models\School;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(Request $request): View
    {
        $query = School::withCount(['users', 'students'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schools = $query->paginate(15)->withQueryString();

        return view('schools.index', compact('schools'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'plan' => ['required', 'in:basico,pro,institucional'],
            'admin_name' => ['required', 'string', 'max:120'],
            'admin_email' => ['required', 'email', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:6'],
            'phone' => ['nullable', 'string', 'max:40'],
        ], [
            'admin_email.unique' => 'Ese correo ya está registrado para otro usuario.',
        ]);

        DB::transaction(function () use ($data) {
            $base = Str::slug($data['name']) ?: 'colegio';
            $slug = $base;
            $i = 1;
            while (School::where('slug', $slug)->exists()) {
                $slug = $base.'-'.(++$i);
            }

            $school = School::create([
                'name' => $data['name'],
                'slug' => $slug,
                'plan' => $data['plan'],
                'status' => 'activo',
                'trial_ends_at' => now()->addDays(30),
                'email' => $data['admin_email'],
                'phone' => $data['phone'] ?? null,
            ]);

            $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
                'name' => 'Administrador',
                'description' => 'Acceso total al sistema',
            ]);

            User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'role_id' => $adminRole->id,
                'school_id' => $school->id,
                'is_active' => true,
            ]);

            Setting::create([
                'school_id' => $school->id,
                'school_name' => $school->name,
                'academic_year' => date('Y') . ' - ' . (date('Y') + 1),
                'active_period' => '1er Trimestre',
                'currency' => '$',
                'tuition_amount' => 1499,
            ]);
        });

        return redirect()->route('schools.index')->with('success', 'Colegio dado de alta exitosamente con su cuenta de administrador.');
    }

    public function show(School $school): View
    {
        $stats = [
            'users' => User::withoutGlobalScopes()->where('school_id', $school->id)->count(),
            'students' => Student::withoutGlobalScopes()->where('school_id', $school->id)->count(),
            'teachers' => Teacher::withoutGlobalScopes()->where('school_id', $school->id)->count(),
            'income' => Payment::withoutGlobalScopes()->where('school_id', $school->id)->where('status', 'pagado')->sum('amount'),
        ];

        $admins = User::withoutGlobalScopes()->where('school_id', $school->id)
            ->whereHas('role', fn ($q) => $q->where('slug', 'admin'))
            ->get();

        return view('schools.show', compact('school', 'stats', 'admins'));
    }

    public function update(Request $request, School $school): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'plan' => ['required', 'in:basico,pro,institucional'],
            'status' => ['required', 'in:activo,suspendido'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
        ]);

        $school->update($data);

        return redirect()->route('schools.show', $school)->with('success', 'Colegio actualizado.');
    }

    public function destroy(School $school): RedirectResponse
    {
        $name = $school->name;

        // No permitir eliminar si es el colegio actual demo id 1 por seguridad
        if ($school->id === 1) {
            return back()->with('error', 'El colegio principal del sistema no se puede eliminar. Puedes suspenderlo.');
        }

        DB::transaction(function () use ($school) {
            User::withoutGlobalScopes()->where('school_id', $school->id)->delete();
            Setting::withoutGlobalScopes()->where('school_id', $school->id)->delete();
            $school->delete();
        });

        return redirect()->route('schools.index')->with('success', "Colegio '{$name}' cancelado y eliminado de la plataforma.");
    }
}
