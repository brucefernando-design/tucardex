@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nombre <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $user->name ?? '') }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Correo <span class="text-danger">*</span></label><input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-control @error('email') is-invalid @enderror" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6">
        <label class="form-label">Rol <span class="text-danger">*</span></label>
        <select name="role_id" class="form-select" required>@foreach($roles as $r)<option value="{{ $r->id }}" @selected(old('role_id', $user->role_id ?? '')==$r->id)>{{ $r->name }}</option>@endforeach</select>
    </div>
    <div class="col-md-6"><label class="form-label">Teléfono</label><input name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">Contraseña {{ isset($user) ? '(dejar vacío para mantener)' : '' }} @if(!isset($user))<span class="text-danger">*</span>@endif</label><input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}></div>
    <div class="col-md-6 d-flex align-items-end">
        <label class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $user->is_active ?? true))> Cuenta activa</label>
    </div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar</button>
    <a href="{{ route('users.index') }}" class="btn btn-light">Cancelar</a>
</div>
