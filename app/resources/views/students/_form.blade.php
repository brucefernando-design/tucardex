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
    
    <!-- Correo del Alumno para su propia cuenta (Opcional, rol estudiante) -->
    <div class="col-md-6">
        <label class="form-label">
            <i class="bi bi-person-badge text-primary me-1"></i> Correo Personal del Alumno (Opcional)
        </label>
        <input type="email" name="email" value="{{ old('email', $student->email ?? '') }}" class="form-control @error('email') is-invalid @enderror" placeholder="alumno@correo.com">
        <div class="form-text text-muted">
            Si se ingresa, se crea/mantiene la cuenta de acceso exclusivo para el <strong>Alumno</strong> (rol estudiante).
        </div>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Teléfono del Alumno</label>
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
    <div class="col-md-5"><label class="form-label">Dirección</label><input name="address" value="{{ old('address', $student->address ?? '') }}" class="form-control" placeholder="Calle, número, colonia, CP"></div>
    <div class="col-md-3">
        <label class="form-label">Estado <span class="text-danger">*</span></label>
        <select name="status" class="form-select">
            @foreach(['activo','inactivo','retirado'] as $st)<option value="{{ $st }}" @selected(old('status', $student->status ?? 'activo')==$st)>{{ ucfirst($st) }}</option>@endforeach
        </select>
    </div>

    <!-- SECCIÓN: TUTORES / PADRES DE FAMILIA (0..N) -->
    <div class="col-12 mt-4 pt-3 border-top">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h5 class="mb-0 text-primary"><i class="bi bi-people-fill me-1"></i> Tutores / Padres de Familia</h5>
                <div class="small text-muted">Cuentas con rol <strong>Padre / Tutor</strong> para consultar calificaciones, asistencia, tareas y estados de cuenta. Un mismo tutor puede tener varios hijos vinculados.</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-guardian">
                <i class="bi bi-plus-circle me-1"></i> Agregar Tutor
            </button>
        </div>

        <div id="guardians-container" class="d-flex flex-column gap-3 mt-3">
            @php
                $existingGuardians = isset($student) ? $student->guardians : collect();
            @endphp

            @forelse($existingGuardians as $index => $guardian)
                <div class="card p-3 guardian-row border shadow-none" style="background:#fcfcfd;">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="guardians[{{ $index }}][name]" value="{{ old("guardians.{$index}.name", $guardian->name) }}" class="form-control form-control-sm" placeholder="Ej. Roberto Sánchez" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="guardians[{{ $index }}][email]" value="{{ old("guardians.{$index}.email", $guardian->email) }}" class="form-control form-control-sm" placeholder="correo@tutor.com" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Teléfono</label>
                            <input type="text" name="guardians[{{ $index }}][phone]" value="{{ old("guardians.{$index}.phone", $guardian->phone) }}" class="form-control form-control-sm" placeholder="5512345678">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Parentesco</label>
                            <select name="guardians[{{ $index }}][relationship]" class="form-select form-select-sm">
                                @php $rel = old("guardians.{$index}.relationship", $guardian->pivot->relationship ?? 'Padre'); @endphp
                                <option value="Padre" @selected($rel == 'Padre')>Padre</option>
                                <option value="Madre" @selected($rel == 'Madre')>Madre</option>
                                <option value="Tutor Legal" @selected($rel == 'Tutor Legal')>Tutor Legal</option>
                                <option value="Abuelo/a" @selected($rel == 'Abuelo/a')>Abuelo/a</option>
                                <option value="Tío/a" @selected($rel == 'Tío/a')>Tío/a</option>
                                <option value="Otro" @selected($rel == 'Otro')>Otro</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-center mb-1">
                            <div class="form-check">
                                <input type="checkbox" name="guardians[{{ $index }}][is_primary]" value="1" class="form-check-input" id="prim_{{ $index }}" @checked(old("guardians.{$index}.is_primary", $guardian->pivot->is_primary ?? false))>
                                <label class="form-check-label small" for="prim_{{ $index }}">Titular</label>
                            </div>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-guardian" title="Quitar tutor de este alumno">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Fila inicial por defecto si es nuevo registro o registro legacy -->
                <div class="card p-3 guardian-row border shadow-none" style="background:#fcfcfd;">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Nombre del Tutor</label>
                            <input type="text" name="guardians[0][name]" value="{{ old('guardians.0.name', $student->guardian_name ?? '') }}" class="form-control form-control-sm" placeholder="Ej. Roberto Sánchez">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Correo del Tutor (Acceso)</label>
                            <input type="email" name="guardians[0][email]" value="{{ old('guardians.0.email', '') }}" class="form-control form-control-sm" placeholder="padre@correo.com">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Teléfono</label>
                            <input type="text" name="guardians[0][phone]" value="{{ old('guardians.0.phone', $student->guardian_phone ?? '') }}" class="form-control form-control-sm" placeholder="5512345678">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Parentesco</label>
                            <select name="guardians[0][relationship]" class="form-select form-select-sm">
                                <option value="Padre">Padre</option>
                                <option value="Madre">Madre</option>
                                <option value="Tutor Legal" selected>Tutor Legal</option>
                                <option value="Abuelo/a">Abuelo/a</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-center mb-1">
                            <div class="form-check">
                                <input type="checkbox" name="guardians[0][is_primary]" value="1" class="form-check-input" id="prim_0" checked>
                                <label class="form-check-label small" for="prim_0">Titular</label>
                            </div>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-guardian" title="Quitar tutor">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar Estudiante</button>
    <a href="{{ route('students.index') }}" class="btn btn-light">Cancelar</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let guardianIndex = {{ max(10, isset($existingGuardians) ? $existingGuardians->count() + 1 : 1) }};
    const container = document.getElementById('guardians-container');
    const btnAdd = document.getElementById('btn-add-guardian');

    if (btnAdd && container) {
        btnAdd.addEventListener('click', function() {
            const idx = guardianIndex++;
            const tpl = `
                <div class="card p-3 guardian-row border shadow-none" style="background:#fcfcfd;">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="guardians[${idx}][name]" class="form-control form-control-sm" placeholder="Ej. Roberto Sánchez" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" name="guardians[${idx}][email]" class="form-control form-control-sm" placeholder="correo@tutor.com" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Teléfono</label>
                            <input type="text" name="guardians[${idx}][phone]" class="form-control form-control-sm" placeholder="5512345678">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Parentesco</label>
                            <select name="guardians[${idx}][relationship]" class="form-select form-select-sm">
                                <option value="Padre">Padre</option>
                                <option value="Madre">Madre</option>
                                <option value="Tutor Legal" selected>Tutor Legal</option>
                                <option value="Abuelo/a">Abuelo/a</option>
                                <option value="Tío/a">Tío/a</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-center mb-1">
                            <div class="form-check">
                                <input type="checkbox" name="guardians[${idx}][is_primary]" value="1" class="form-check-input" id="prim_${idx}">
                                <label class="form-check-label small" for="prim_${idx}">Titular</label>
                            </div>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-guardian" title="Quitar tutor">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
            container.insertAdjacentHTML('beforeend', tpl);
        });

        container.addEventListener('click', function(e) {
            const btnRemove = e.target.closest('.btn-remove-guardian');
            if (btnRemove) {
                const row = btnRemove.closest('.guardian-row');
                if (row) row.remove();
            }
        });
    }
});
</script>
