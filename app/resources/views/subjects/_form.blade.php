@csrf
<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nombre <span class="text-danger">*</span></label><input name="name" value="{{ old('name', $subject->name ?? '') }}" class="form-control" placeholder="Ej: Matemática" required></div>
    <div class="col-md-3"><label class="form-label">Código <span class="text-danger">*</span></label><input name="code" value="{{ old('code', $subject->code ?? '') }}" class="form-control" placeholder="Ej: MAT" required></div>
    <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select name="status" class="form-select">@foreach(['activo','inactivo'] as $st)<option value="{{ $st }}" @selected(old('status', $subject->status ?? 'activo')==$st)>{{ ucfirst($st) }}</option>@endforeach</select>
    </div>
    <div class="col-md-6"><label class="form-label">Área curricular</label><input name="area" value="{{ old('area', $subject->area ?? '') }}" class="form-control" placeholder="Ej: Ciencias exactas"></div>
    <div class="col-12"><label class="form-label">Descripción</label><textarea name="description" class="form-control" rows="3">{{ old('description', $subject->description ?? '') }}</textarea></div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar</button>
    <a href="{{ route('subjects.index') }}" class="btn btn-light">Cancelar</a>
</div>
