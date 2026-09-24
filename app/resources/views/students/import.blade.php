@extends('layouts.app')
@section('title', 'Importar estudiantes')

@section('content')
<div class="page-head">
    <div><h1>Importar estudiantes</h1><div class="breadcrumb-mini">Carga masiva desde un archivo CSV</div></div>
    <a href="{{ route('students.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="grid-2">
    <div class="card card-accent">
        <div class="card-header"><span class="title"><i class="bi bi-upload"></i> Subir archivo</span></div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger py-2"><i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}</div>
            @endif
            <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">@csrf
                <div class="mb-3">
                    <label class="form-label">Archivo CSV <span class="text-danger">*</span></label>
                    <input type="file" name="file" accept=".csv,.txt" class="form-control" required>
                    <div class="form-text">Formato CSV separado por comas, con encabezados en la primera fila.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Curso por defecto (opcional)</label>
                    <select name="course_id" class="form-select">
                        <option value="">Usar la columna "curso" del archivo</option>
                        @foreach($courses as $c)<option value="{{ $c->id }}">{{ $c->name }} "{{ $c->section }}"</option>@endforeach
                    </select>
                    <div class="form-text">Se aplica a las filas que no especifiquen un curso válido.</div>
                </div>
                <button class="btn btn-brand btn-icon"><i class="bi bi-cloud-upload"></i> Importar estudiantes</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-info-circle"></i> Instrucciones</span></div>
        <div class="card-body">
            <p>El archivo debe tener estas columnas (la primera fila son los encabezados):</p>
            <table class="table table-sm">
                <thead><tr><th>Columna</th><th>Ejemplo</th></tr></thead>
                <tbody>
                    <tr><td><code>nombres</code></td><td>Juan</td></tr>
                    <tr><td><code>apellidos</code></td><td>Pérez López</td></tr>
                    <tr><td><code>ci</code></td><td>12345678</td></tr>
                    <tr><td><code>fecha_nacimiento</code></td><td>2012-05-10</td></tr>
                    <tr><td><code>genero</code></td><td>M / F</td></tr>
                    <tr><td><code>telefono</code></td><td>70011223</td></tr>
                    <tr><td><code>apoderado</code></td><td>María López</td></tr>
                    <tr><td><code>telefono_apoderado</code></td><td>70554433</td></tr>
                    <tr><td><code>curso</code></td><td>1ro de Secundaria</td></tr>
                </tbody>
            </table>
            <a href="{{ route('students.template') }}" class="btn btn-outline-secondary btn-icon w-100"><i class="bi bi-download"></i> Descargar plantilla CSV</a>
            <p class="text-muted small mt-3 mb-0"><i class="bi bi-lightbulb"></i> El código de matrícula se genera automáticamente. Solo <code>nombres</code> y <code>apellidos</code> son obligatorios.</p>
        </div>
    </div>
</div>
@endsection
