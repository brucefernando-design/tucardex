@extends('layouts.app')
@section('title', 'Mi perfil')

@section('content')
<div class="page-head"><div><h1>Mi perfil</h1><div class="breadcrumb-mini">Datos de tu cuenta</div></div></div>

<div class="card card-accent" style="max-width:680px">
    <div class="card-header"><span class="title"><i class="bi bi-person-gear"></i> Información personal</span></div>
    <div class="card-body">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
            <div class="d-flex align-items-center gap-3 mb-4">
                @if($user->avatar_url)<img src="{{ $user->avatar_url }}" class="avatar-sm" style="width:64px;height:64px;object-fit:cover">@else<div class="avatar-sm" style="width:64px;height:64px;font-size:22px">{{ $user->initials() }}</div>@endif
                <div class="flex-grow-1">
                    <h5 class="mb-0">{{ $user->name }}</h5><span class="text-muted">{{ optional($user->role)->name }}</span>
                    <input type="file" name="avatar" accept="image/*" class="form-control form-control-sm mt-2 @error('avatar') is-invalid @enderror" style="max-width:320px">
                    @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nombre</label><input name="name" value="{{ old('name', $user->name) }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Correo</label><input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-6"><label class="form-label">Teléfono</label><input name="phone" value="{{ old('phone', $user->phone) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Nueva contraseña</label><input type="password" name="password" class="form-control" placeholder="Dejar vacío para mantener"></div>
                <div class="col-md-6"><label class="form-label">Confirmar contraseña</label><input type="password" name="password_confirmation" class="form-control"></div>
            </div>
            <div class="mt-4"><button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar cambios</button></div>
        </form>
    </div>
</div>
@endsection
