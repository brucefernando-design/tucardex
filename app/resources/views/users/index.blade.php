@extends('layouts.app')
@section('title', 'Usuarios y Roles')

@section('content')
<div class="page-head"><div><h1>Usuarios y Roles</h1><div class="breadcrumb-mini">Cuentas de acceso al sistema</div></div>
    <a href="{{ route('users.create') }}" class="btn btn-brand btn-icon"><i class="bi bi-person-plus"></i> Nuevo usuario</a></div>

<div class="card"><div class="card-body">
    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th class="ps-3">Usuario</th><th>Correo</th><th>Rol</th><th>Teléfono</th><th>Estado</th><th class="text-end pe-3">Acciones</th></tr></thead>
        <tbody>
        @foreach($users as $u)
            <tr>
                <td class="ps-3"><span class="avatar-sm me-2">{{ $u->initials() }}</span>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td><span class="badge bg-light text-dark border">{{ optional($u->role)->name ?? '—' }}</span></td>
                <td>{{ $u->phone ?? '—' }}</td>
                <td><span class="badge-soft {{ $u->is_active ? 'badge-activo' : 'badge-inactivo' }}">{{ $u->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                <td class="text-end pe-3">
                    <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar usuario?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @endforeach
        </tbody></table></div>
    {{ $users->links() }}
</div></div>
@endsection
