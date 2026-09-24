@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nombre del grado/grupo <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $course->name ?? '') }}" class="form-control" placeholder="Ej: 1ro de Secundaria" required></div>
    <div class="col-md-3">
        <label class="form-label">Nivel <span class="text-danger">*</span></label>
        <select name="level" class="form-select">@foreach(['Preescolar','Primaria','Secundaria','Bachillerato'] as $l)<option value="{{ $l }}" @selected(old('level', $course->level ?? 'Secundaria')==$l)>{{ $l }}</option>@endforeach</select>
    </div>
    <div class="col-md-3"><label class="form-label">Grado</label><input name="grade" value="{{ old('grade', $course->grade ?? '') }}" class="form-control" placeholder="Ej: 1ro"></div>
    <div class="col-md-3"><label class="form-label">Grupo (Sección) <span class="text-danger">*</span></label><input name="section" value="{{ old('section', $course->section ?? 'A') }}" class="form-control" required></div>
    <div class="col-md-3">
        <label class="form-label">Turno <span class="text-danger">*</span></label>
        <select name="shift" class="form-select">@foreach(['Mañana','Tarde','Noche'] as $s)<option value="{{ $s }}" @selected(old('shift', $course->shift ?? 'Mañana')==$s)>{{ $s }}</option>@endforeach</select>
    </div>
    <div class="col-md-3"><label class="form-label">Capacidad <span class="text-danger">*</span></label><input type="number" name="capacity" value="{{ old('capacity', $course->capacity ?? 35) }}" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Ciclo Escolar <span class="text-danger">*</span></label><input name="academic_year" value="{{ old('academic_year', $course->academic_year ?? date('Y')) }}" class="form-control" required></div>
    <div class="col-md-6">
        <label class="form-label">Profesor Titular</label>
        <select name="tutor_id" class="form-select"><option value="">Sin asignar</option>
            @foreach($teachers as $t)<option value="{{ $t->id }}" @selected(old('tutor_id', $course->tutor_id ?? '')==$t->id)>{{ $t->full_name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Estado</label>
        <select name="status" class="form-select">@foreach(['activo','inactivo'] as $st)<option value="{{ $st }}" @selected(old('status', $course->status ?? 'activo')==$st)>{{ ucfirst($st) }}</option>@endforeach</select>
    </div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar</button>
    <a href="{{ route('courses.index') }}" class="btn btn-light">Cancelar</a>
</div>
