@csrf
<div class="d-flex align-items-center gap-3 mb-3 p-3" style="background:#f7faf9;border-radius:12px">
    <div style="width:70px;height:70px;border-radius:14px;overflow:hidden;background:linear-gradient(135deg,var(--brand-2),var(--brand-3));display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;color:#fff;flex-shrink:0">
        @if(($student->photo_url ?? null))<img src="{{ $student->photo_url }}" alt="foto" style="width:100%;height:100%;object-fit:cover">@else<i class="bi bi-person"></i>@endif
    </div>
    <div class="flex-grow-1">
        <label class="form-label">Foto del estudiante</label>
        <input type="file" name="photo" accept="image/*" class="form-control @error('photo') is-invalid @enderror">
        <div class="form-text">JPG/PNG, máx 2MB. Se usa en la ficha y la credencial escolar.</div>
        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nombres <span class="text-danger">*</span></label><input name="first_name" value="{{ old('first_name', $student->first_name ?? '') }}" class="form-control @error('first_name') is-invalid @enderror" required>@error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label">Apellidos <span class="text-danger">*</span></label><input name="last_name" value="{{ old('last_name', $student->last_name ?? '') }}" class="form-control @error('last_name') is-invalid @enderror" required>@error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4"><label class="form-label">Código / Matrícula</label><input name="code" value="{{ old('code', $student->code ?? '') }}" class="form-control" placeholder="Auto si se deja vacío"></div>
    <div class="col-md-4"><label class="form-label">CURP (18 caracteres)</label><input name="curp" value="{{ old('curp', $student->curp ?? '') }}" class="form-control text-uppercase @error('curp') is-invalid @enderror" maxlength="18" placeholder="AAAA000000HXXXXX00">@error('curp')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4"><label class="form-label">Identificación adicional (opcional)</label><input name="dni" value="{{ old('dni', $student->dni ?? '') }}" class="form-control"></div>
    
    <!-- Correo Electrónico Requerido para Acceso y Notificaciones -->
    <div class="col-md-8">
        <label class="form-label fw-bold text-dark">
            <i class="bi bi-envelope-at text-primary me-1"></i> Correo Electrónico (del Estudiante o Tutor) <span class="text-danger">*</span>
        </label>
        <input type="email" name="email" value="{{ old('email', $student->email ?? '') }}" class="form-control @error('email') is-invalid @enderror" placeholder="ejemplo@correo.com" required>
        <div class="form-text text-muted">
            <i class="bi bi-shield-lock-fill text-success"></i> <strong>Cuenta de Acceso al Sistema:</strong> Con este correo el estudiante o padre podrá iniciar sesión en TuCardex y recibir citatorios o avisos. Su contraseña inicial será su matrícula (ej: {{ $student->code ?? 'EST-00001' }}).
        </div>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input name="phone" value="{{ old('phone', $student->phone ?? '') }}" class="form-control" placeholder="Teléfono de contacto">
    </div>

    <div class="col-md-4"><label class="form-label">Fecha de nacimiento</label><input type="date" name="birth_date" value="{{ old('birth_date', optional($student->birth_date ?? null)->format('Y-m-d')) }}" class="form-control"></div>
    <div class="col-md-4">
        <label class="form-label">Género</label>
        <select name="gender" class="form-select">
            <option value="">—</option>
            @foreach(['M'=>'Masculino','F'=>'Femenino','Otro'=>'Otro'] as $k=>$v)<option value="{{ $k }}" @selected(old('gender', $student->gender ?? '')==$k)>{{ $v }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Curso / Grado</label>
        <select name="course_id" class="form-select">
            <option value="">Sin asignar</option>
            @foreach($courses as $c)<option value="{{ $c->id }}" @selected(old('course_id', $student->course_id ?? '')==$c->id)>{{ $c->name }} "{{ $c->section }}"</option>@endforeach
        </select>
    </div>

    <div class="col-md-4"><label class="form-label">Fecha de matrícula</label><input type="date" name="enrollment_date" value="{{ old('enrollment_date', optional($student->enrollment_date ?? null)->format('Y-m-d') ?? date('Y-m-d')) }}" class="form-control"></div>
    <div class="col-md-8"><label class="form-label">Dirección</label><input name="address" value="{{ old('address', $student->address ?? '') }}" class="form-control" placeholder="Calle, número, colonia, CP"></div>
    
    <div class="col-md-6"><label class="form-label">Padre, Madre o Tutor</label><input name="guardian_name" value="{{ old('guardian_name', $student->guardian_name ?? '') }}" class="form-control" placeholder="Nombre completo del tutor"></div>
    <div class="col-md-3"><label class="form-label">Teléfono del Tutor</label><input name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone ?? '') }}" class="form-control"></div>
    <div class="col-md-3">
        <label class="form-label">Estado <span class="text-danger">*</span></label>
        <select name="status" class="form-select">
            @foreach(['activo','inactivo','retirado'] as $st)<option value="{{ $st }}" @selected(old('status', $student->status ?? 'activo')==$st)>{{ ucfirst($st) }}</option>@endforeach
        </select>
    </div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar Estudiante</button>
    <a href="{{ route('students.index') }}" class="btn btn-light">Cancelar</a>
</div>
