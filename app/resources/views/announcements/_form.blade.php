@csrf
<div class="row g-3">
    <div class="col-12"><label class="form-label">Título <span class="text-danger">*</span></label><input name="title" value="{{ old('title', $announcement->title ?? '') }}" class="form-control" required></div>
    <div class="col-md-6">
        <label class="form-label">Dirigido a</label>
        <select name="audience" class="form-select">@foreach(['todos','docentes','estudiantes','padres'] as $a)<option value="{{ $a }}" @selected(old('audience', $announcement->audience ?? 'todos')==$a)>{{ ucfirst($a) }}</option>@endforeach</select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Estado</label>
        <select name="status" class="form-select">@foreach(['publicado','borrador'] as $s)<option value="{{ $s }}" @selected(old('status', $announcement->status ?? 'publicado')==$s)>{{ ucfirst($s) }}</option>@endforeach</select>
    </div>
    <div class="col-12"><label class="form-label">Mensaje <span class="text-danger">*</span></label><textarea name="body" class="form-control" rows="6" required>{{ old('body', $announcement->body ?? '') }}</textarea></div>
    <div class="col-12">
        <label class="form-check">
            <input type="checkbox" name="send_email" value="1" class="form-check-input" @checked(old('send_email'))>
            <i class="bi bi-envelope-paper me-1"></i> Enviar también por correo a los destinatarios
        </label>
        <div class="form-text">Se enviará a los correos registrados según la audiencia seleccionada.</div>
    </div>
</div>
<div class="mt-4 d-flex gap-2">
    <button class="btn btn-brand btn-icon"><i class="bi bi-send"></i> Publicar</button>
    <a href="{{ route('announcements.index') }}" class="btn btn-light">Cancelar</a>
</div>
