<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('role')->latest()->paginate(12);
        $roles = $this->allowedRoles(auth()->user());

        return view('users.index', compact('users', 'roles'));
    }

    public function create(Request $request): View
    {
        $roles = $this->allowedRoles($request->user());

        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $allowedRoleIds = $this->allowedRoles($request->user())->pluck('id')->all();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id', Rule::in($allowedRoleIds)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'role_id.in' => 'El rol seleccionado no es válido o no tienes permisos para asignarlo.',
        ]);

        $data['password'] = Hash::make($data['password']);
        $adminRole = Role::where('slug', 'admin')->first();
        if ($data['role_id'] == optional($adminRole)->id) {
            $school = auth()->user()->school;
            if ($school && ! $school->canAddAdminUser()) {
                return back()->withInput()->with('error', 'El Plan Básico admite únicamente 1 usuario administrador. Actualiza a Plan Profesional para agregar más administradores.');
            }
        }
        $data['is_active'] = $request->boolean('is_active');

        User::create($data);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(Request $request, User $user): View
    {
        $roles = $this->allowedRoles($request->user());

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $allowedRoleIds = $this->allowedRoles($request->user())->pluck('id')->all();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id', Rule::in($allowedRoleIds)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'role_id.in' => 'El rol seleccionado no es válido o no tienes permisos para asignarlo.',
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $adminRole = Role::where('slug', 'admin')->first();
        if ($data['role_id'] == optional($adminRole)->id) {
            $school = auth()->user()->school;
            if ($school && ! $school->canAddAdminUser()) {
                return back()->withInput()->with('error', 'El Plan Básico admite únicamente 1 usuario administrador. Actualiza a Plan Profesional para agregar más administradores.');
            }
        }
        $data['is_active'] = $request->boolean('is_active');

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }

    /**
     * Retorna los roles permitidos según los privilegios del usuario autenticado.
     * Solo superadmin puede asignar el rol superadmin.
     * Un administrador de colegio solo puede asignar: admin, secretaria, docente, estudiante.
     */
    private function allowedRoles(?User $currentUser): Collection
    {
        $query = Role::orderBy('name');

        if (! $currentUser?->isSuperAdmin()) {
            $query->whereIn('slug', ['admin', 'secretaria', 'docente', 'estudiante']);
        }

        return $query->get();
    }
}
