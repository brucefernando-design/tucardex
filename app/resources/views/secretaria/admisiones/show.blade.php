@extends('layouts.app')
@section('title', 'Expediente Aspirante ' . $admission->folio)

@section('content')
<div class="page-head">
    <div>
        <h1>Ficha de Aspirante: {{ $admission->full_name }}</h1>
        <div class="breadcrumb-mini">Admisiones / Folio <strong class="font-monospace text-primary">{{ $admission->folio }}</strong></div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('secretaria.admisiones.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver a Lista</a>
        <a href="{{ route('admissions.public_pdf', ['slug' => $admission->school->slug, 'folio' => $admission->folio]) }}" target="_blank" class="btn btn-outline-secondary btn-icon">
            <i class="bi bi-file-earmark-pdf"></i> Imprimir Ficha PDF
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Columna Izquierda: Datos del Aspirante y Documentos --}}
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="title fw-bold"><i class="bi bi-person-badge text-success me-2"></i> Información del Aspirante</span>
                {!! $admission->statusBadge() !!}
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <small class="text-muted d-block" style="font-size:11px;">NOMBRE COMPLETO</small>
                        <strong>{{ $admission->full_name }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block" style="font-size:11px;">CURP</small>
                        <strong class="font-monospace">{{ $admission->curp ?? '—' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block" style="font-size:11px;">FECHA DE NACIMIENTO</small>
                        <span>{{ $admission->birth_date ? $admission->birth_date->format('d/m/Y') : '—' }} ({{ $admission->gender == 'M' ? 'Masculino' : ($admission->gender == 'F' ? 'Femenino' : '—') }})</span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block" style="font-size:11px;">GRADO SOLICITADO</small>
                        <span class="badge bg-light text-dark border">{{ optional($admission->course)->name ?? 'General' }}</span>
                    </div>
                    @if($admission->previous_school)
                    <div class="col-12">
                        <small class="text-muted d-block" style="font-size:11px;">ESCUELA DE PROCEDENCIA</small>
                        <span>{{ $admission->previous_school }}</span>
                    </div>
                    @endif
                    @if($admission->medical_notes)
                    <div class="col-12">
                        <small class="text-muted d-block" style="font-size:11px;">OBSERVACIONES MÉDICAS / ALERGIAS</small>
                        <div class="p-2 bg-light rounded text-danger small"><i class="bi bi-heart-pulse me-1"></i> {{ $admission->medical_notes }}</div>
                    </div>
                    @endif
                </div>

                <h6 class="fw-bold border-top pt-3 mb-3 text-dark">Datos del Padre, Madre o Tutor</h6>
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <small class="text-muted d-block" style="font-size:11px;">NOMBRE DEL TUTOR</small>
                        <strong>{{ $admission->guardian_name }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block" style="font-size:11px;">PARENTESCO</small>
                        <span>{{ $admission->guardian_relationship }}</span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block" style="font-size:11px;">WHATSAPP / TELÉFONO</small>
                        <a href="https://wa.me/52{{ preg_replace('/\D/', '', $admission->guardian_phone) }}" target="_blank" class="btn btn-sm btn-outline-success mt-1">
                            <i class="bi bi-whatsapp me-1"></i> +52 {{ $admission->guardian_phone }}
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block" style="font-size:11px;">CORREO ELECTRÓNICO</small>
                        <a href="mailto:{{ $admission->guardian_email }}" class="small">{{ $admission->guardian_email }}</a>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block" style="font-size:11px;">DOMICILIO</small>
                        <span>{{ $admission->address ?? 'No proporcionado' }}</span>
                    </div>
                </div>

                <h6 class="fw-bold border-top pt-3 mb-3 text-dark">Documentación Digital Adjuntada</h6>
                <div class="list-group list-group-flush border rounded">
                    <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div>
                            <i class="bi bi-file-earmark-person fs-5 text-primary me-2"></i>
                            <strong>Acta de Nacimiento</strong>
                        </div>
                        @if($admission->birth_certificate_path)
                            <a href="{{ asset('storage/' . $admission->birth_certificate_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i> Ver / Descargar
                            </a>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">No adjuntado</span>
                        @endif
                    </div>

                    <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div>
                            <i class="bi bi-card-heading fs-5 text-primary me-2"></i>
                            <strong>CURP Digital</strong>
                        </div>
                        @if($admission->curp_path)
                            <a href="{{ asset('storage/' . $admission->curp_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i> Ver / Descargar
                            </a>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">No adjuntado</span>
                        @endif
                    </div>

                    <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div>
                            <i class="bi bi-house fs-5 text-primary me-2"></i>
                            <strong>Comprobante de Domicilio</strong>
                        </div>
                        @if($admission->address_proof_path)
                            <a href="{{ asset('storage/' . $admission->address_proof_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i> Ver / Descargar
                            </a>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">No adjuntado</span>
                        @endif
                    </div>

                    <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                        <div>
                            <i class="bi bi-award fs-5 text-primary me-2"></i>
                            <strong>Boleta o Certificado Anterior</strong>
                        </div>
                        @if($admission->previous_grades_path)
                            <a href="{{ asset('storage/' . $admission->previous_grades_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i> Ver / Descargar
                            </a>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">No adjuntado</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Columna Derecha: Acciones Administrativas y Matricular --}}
    <div class="col-lg-5">
        {{-- Tarjeta 1: Estado y Notas Internas --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
            <div class="card-header bg-white py-3 border-bottom">
                <span class="title fw-bold"><i class="bi bi-gear-wide-connected text-primary me-2"></i> Estado de la Solicitud</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('secretaria.admisiones.update_status', $admission) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Estatus del Aspirante</label>
                        <select name="status" class="form-select" {{ $admission->status=='matriculada' ? 'disabled' : '' }}>
                            <option value="pendiente" @selected($admission->status=='pendiente')>🟡 Pendiente</option>
                            <option value="en_revision" @selected($admission->status=='en_revision')>🔵 En Revisión</option>
                            <option value="aceptada" @selected($admission->status=='aceptada')>🟢 Aceptada (Lista para Matrícula)</option>
                            <option value="rechazada" @selected($admission->status=='rechazada')>🔴 Rechazada</option>
                            @if($admission->status=='matriculada')
                                <option value="matriculada" selected>🎓 Matriculada como Alumno</option>
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Notas Internas de Control Escolar</label>
                        <textarea name="internal_notes" class="form-control" rows="3" placeholder="Observaciones de prefectura, documentos faltantes o acuerdos con los padres...">{{ old('internal_notes', $admission->internal_notes) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-outline-dark btn-sm w-100" {{ $admission->status=='matriculada' ? 'disabled' : '' }}>
                        <i class="bi bi-save me-1"></i> Guardar Estatus y Notas
                    </button>
                </form>
            </div>
        </div>

        {{-- Tarjeta 2: 1-Click Matricular Alumno Oficial --}}
        <div id="matricular" class="card shadow-sm border-0" style="border-radius:14px; background:linear-gradient(135deg, #f0fdf4, #dcfce7); border:1.5px solid #bbf7d0 !important;">
            <div class="card-body p-4">
                <span class="small text-uppercase fw-bold text-success tracking-wider d-block mb-1">
                    <i class="bi bi-mortarboard-fill me-1"></i> Alta Oficial Escolar
                </span>
                <h5 class="fw-bold text-dark mb-2">Matricular como Alumno Oficial</h5>
                <p class="small text-muted mb-3">
                    Convierte a este aspirante en un estudiante activo del colegio. El sistema generará su matrícula oficial, creará la cuenta de acceso para el padre/tutor y enviará un WhatsApp de felicitación.
                </p>

                @if($admission->status === 'matriculada')
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-0 shadow-sm">
                        <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                        <div>
                            <strong>¡Aspirante ya matriculado!</strong><br>
                            Matrícula: <strong>{{ optional($admission->student)->code }}</strong> · 
                            <a href="{{ route('students.show', $admission->student_id) }}" class="fw-bold text-success text-decoration-none">Ver Expediente del Alumno &raquo;</a>
                        </div>
                    </div>
                @else
                    <form action="{{ route('secretaria.admisiones.matricular', $admission) }}" method="POST" onsubmit="return confirm('¿Confirmas que deseas matricular a {{ $admission->full_name }} en este curso? Se creará su expediente oficial y usuario de tutor.');">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Asignar al Curso / Grado *</label>
                            <select name="course_id" class="form-select bg-white" required>
                                <option value="">Selecciona el grupo...</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}" @selected($admission->course_id == $c->id)>{{ $c->name }} "{{ $c->section }}" ({{ $c->shift ?? 'Matutino' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-3 fw-bold fs-6 shadow-sm" style="border-radius:12px;">
                            <i class="bi bi-mortarboard-fill me-1"></i> Confirmar y Matricular Alumno
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
