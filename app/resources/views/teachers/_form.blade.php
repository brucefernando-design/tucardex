@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nombres <span class="text-danger">*</span></label><input name="first_name" value="{{ old('first_name', $teacher->first_name ?? '') }}" class="form-control @error('first_name') is-invalid @enderror" required>@error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label">Apellidos <span class="text-danger">*</span></label><input name="last_name" value="{{ old('last_name', $teacher->last_name ?? '') }}" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Código</label><input name="code" value="{{ old('code', $teacher->code ?? '') }}" class="form-control" placeholder="Auto si vacío"></div>
    <div class="col-md-4"><label class="form-label">CURP / Identificación oficial (INE)</label><input name="dni" value="{{ old('dni', $teacher->dni ?? '') }}" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Especialidad</label><input name="specialty" value="{{ old('specialty', $teacher->specialty ?? '') }}" class="form-control" placeholder="Ej: Matemática"></div>
    <div class="col-md-6"><label class="form-label">Correo</label><input name="email" value="{{ old('email', $teacher->email ?? '') }}" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Teléfono</label><input name="phone" value="{{ old('phone', $teacher->phone ?? '') }}" class="form-control"></div>
    <div class="col-md-3">
        <label class="form-label">Género</label>
        <select name="gender" class="form-select"><option value="">—</option>
            @foreach(['Masculino','Femenino'] as $g)<option value="{{ $g }}" @selected(old('gender', $teacher->gender ?? '')==$g)>{{ $g }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-4"><label class="form-label">Fecha de nacimiento</label><input type="date" name="birth_date" value="{{ old('birth_date', optional($teacher->birth_date ?? null)->format('Y-m-d')) }}" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Fecha de contratación</label><input type="date" name="hire_date" value="{{ old('hire_date', optional($teacher->hire_date ?? null)->format('Y-m-d')) }}" class="form-control"></div>
    <div class="col-md-4">
        <label class="form-label">Estado <span class="text-danger">*</span></label>
        <select name="status" class="form-select">
            @foreach(['activo','inactivo'] as $st)<option value="{{ $st }}" @selected(old('status', $teacher->status ?? 'activo')==$st)>{{ ucfirst($st) }}</option>@endforeach
        </select>
    </div>
    <div class="col-12"><label class="form-label">Dirección</label><input name="address" value="{{ old('address', $teacher->address ?? '') }}" class="form-control"></div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar</button>
    <a href="{{ route('teachers.index') }}" class="btn btn-light">Cancelar</a>
</div>
