@csrf
<div class="row g-3">
    <div class="col-12"><label class="form-label">Título <span class="text-danger">*</span></label><input name="title" value="{{ old('title', $assignment->title ?? '') }}" class="form-control" required></div>
    <div class="col-md-4">
        <label class="form-label">Curso <span class="text-danger">*</span></label>
        <select name="course_id" class="form-select" required>
            <option value="">Seleccione...</option>
            @foreach($courses as $c)<option value="{{ $c->id }}" @selected(old('course_id', $assignment->course_id ?? '')==$c->id)>{{ $c->name }} "{{ $c->section }}"</option>@endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Materia <span class="text-danger">*</span></label>
        <select name="subject_id" class="form-select" required>
            <option value="">Seleccione...</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}" @selected(old('subject_id', $assignment->subject_id ?? '')==$s->id)>{{ $s->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Docente</label>
        <select name="teacher_id" class="form-select">
            <option value="">Automático / Sin asignar</option>
            @foreach($teachers as $t)<option value="{{ $t->id }}" @selected(old('teacher_id', $assignment->teacher_id ?? '')==$t->id)>{{ $t->full_name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-4"><label class="form-label">Fecha asignación</label><input type="date" name="assigned_date" value="{{ old('assigned_date', optional($assignment->assigned_date ?? null)->format('Y-m-d') ?? date('Y-m-d')) }}" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Fecha de entrega <span class="text-danger">*</span></label><input type="date" name="due_date" value="{{ old('due_date', optional($assignment->due_date ?? null)->format('Y-m-d')) }}" class="form-control" required></div>
    <div class="col-md-4">
        <label class="form-label">Estado</label>
        <select name="status" class="form-select">@foreach(['activa','cerrada'] as $st)<option value="{{ $st }}" @selected(old('status', $assignment->status ?? 'activa')==$st)>{{ ucfirst($st) }}</option>@endforeach</select>
    </div>
    <div class="col-12"><label class="form-label">Descripción / Instrucciones</label><textarea name="description" class="form-control" rows="5">{{ old('description', $assignment->description ?? '') }}</textarea></div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar</button>
    <a href="{{ route('assignments.index') }}" class="btn btn-light">Cancelar</a>
</div>
