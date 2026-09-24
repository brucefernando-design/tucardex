<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Role;
use App\Models\School;
use App\Models\ImpersonationLog;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'price_per_student' => ['nullable', 'numeric', 'min:0'],
            'minimum_monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'trial_ends_at' => ['nullable', 'date'],
            'billing_renews_at' => ['nullable', 'date'],
            'subscription_status' => ['nullable', 'string', 'max:30'],
        ]);

        $school->update($data);

        return redirect()->route('schools.show', $school)->with('success', 'Colegio y condiciones de suscripción actualizadas exitosamente.');
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

    /**
     * Inicia sesión como administrador del colegio seleccionado (Modo Soporte SaaS).
     */
    public function impersonate(School $school): RedirectResponse
    {
        $currentUser = auth()->user();
        if (! $currentUser || ! $currentUser->isSuperAdmin()) {
            abort(403, 'Solo el SuperAdministrador de la plataforma puede usar el modo soporte.');
        }

        $admin = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->whereHas('role', fn ($q) => $q->where('slug', 'admin'))
            ->first();

        if (! $admin) {
            $admin = User::withoutGlobalScopes()->where('school_id', $school->id)->first();
        }

        if (! $admin) {
            return back()->with('error', "El colegio '{$school->name}' no tiene ningún usuario registrado para ingresar.");
        }

        // Registrar en bitácora de auditoría estricta
        $log = ImpersonationLog::create([
            'superadmin_id'            => $currentUser->id,
            'superadmin_email'         => $currentUser->email,
            'school_id'                => $school->id,
            'school_name'              => $school->name,
            'impersonated_user_id'     => $admin->id,
            'impersonated_user_email'  => $admin->email,
            'ip_address'               => request()->ip(),
            'user_agent'               => request()->userAgent(),
            'started_at'               => now(),
        ]);

        session([
            'impersonator_id'     => $currentUser->id,
            'impersonator_school' => $school->id,
            'impersonation_log_id' => $log->id,
        ]);

        Auth::login($admin);

        return redirect()->route('dashboard')->with('success', "Has ingresado como Administrador de {$school->name} en Modo Soporte Técnico. (Acción auditada)");
    }

    /**
     * Sale del modo suplantación y restaura la sesión del SuperAdmin.
     */
    public function leaveImpersonation(): RedirectResponse
    {
        if (! session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $superAdminId = session('impersonator_id');
        $schoolId = session('impersonator_school');
        $logId = session('impersonation_log_id');

        // Cerrar bitácora de auditoría
        if ($logId) {
            ImpersonationLog::where('id', $logId)->update(['ended_at' => now()]);
        }

        $superAdmin = User::find($superAdminId);
        session()->forget(['impersonator_id', 'impersonator_school', 'impersonation_log_id']);

        if ($superAdmin) {
            Auth::login($superAdmin);
        }

        if ($schoolId) {
            return redirect()->route('schools.show', $schoolId)->with('success', 'Sesión de soporte finalizada. Has vuelto a tu Panel SuperAdmin.');
        }

        return redirect()->route('schools.index')->with('success', 'Has vuelto a tu Panel SuperAdmin.');
    }

    /**
     * Alterna rápidamente el estado del colegio entre Activo y Suspendido.
     */
    public function toggleStatus(School $school): RedirectResponse
    {
        if ($school->id === 1 && $school->status === 'activo') {
            return back()->with('error', 'El colegio principal del sistema no debe ser suspendido.');
        }

        $newStatus = $school->status === 'activo' ? 'suspendido' : 'activo';
        $school->update(['status' => $newStatus]);

        $msg = $newStatus === 'activo'
            ? "El colegio '{$school->name}' ha sido REACTIVADO exitosamente."
            : "El colegio '{$school->name}' ha sido SUSPENDIDO. Sus usuarios no podrán ingresar hasta reactivarlo.";

        return back()->with('success', $msg);
    }

    /**
     * Reseteo express de contraseña de un administrador del colegio.
     */
    public function resetAdminPassword(Request $request, School $school): RedirectResponse
    {
        $request->validate([
            'admin_id' => ['required', 'exists:users,id'],
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $admin = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('id', $request->admin_id)
            ->firstOrFail();

        $admin->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', "Contraseña actualizada exitosamente para {$admin->name} ({$admin->email}). La nueva clave ha sido aplicada de forma segura.");
    }
}
