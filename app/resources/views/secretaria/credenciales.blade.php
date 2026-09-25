@extends('layouts.app')

@section('title', 'Generador de Credenciales Escolares')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('secretaria.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle p-1" title="Volver a Secretaría">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0 text-dark">Generador de Credenciales Escolares</h3>
            </div>
            <p class="text-muted small mb-0">Emisión de credenciales con fotografía, código QR de validación y formato individual PVC o planilla por grado/grupo.</p>
        </div>
        @if($selectedCourse && $students->isNotEmpty())
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="dropdown">
                    <button class="btn btn-success rounded-pill px-3 py-2 shadow-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-printer-fill"></i>
                        <span class="fw-semibold">Imprimir Planilla Completa ({{ $students->count() }} Alumnos)</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2" style="min-width:320px;">
                        <li>
                            <a class="dropdown-item p-2 rounded-3" href="{{ route('secretaria.credenciales.grupo', ['course' => $selectedCourse->id, 'tipo' => 'frente_reverso']) }}">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-layout-split text-success fs-5"></i>
                                    <div>
                                        <strong class="d-block text-dark" style="font-size:13px;">Frente y Reverso Juntos (Recomendado)</strong>
                                        <span class="text-muted" style="font-size:11px;">4 alumnos por hoja (anverso y reverso contiguos). Ideal para doblar y enmicar en cartulina u opalina.</span>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item p-2 rounded-3" href="{{ route('secretaria.credenciales.grupo', ['course' => $selectedCourse->id, 'tipo' => 'duplex']) }}">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-arrow-repeat text-primary fs-5"></i>
                                    <div>
                                        <strong class="d-block text-dark" style="font-size:13px;">Impresión Dúplex (Doble Cara)</strong>
                                        <span class="text-muted" style="font-size:11px;">8 alumnos por hoja. Pág. 1 con frentes y Pág. 2 con reversos en espejo para impresoras automáticas a 2 caras.</span>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <!-- Course Filter Selector -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-3">1. Selecciona un Grado / Grupo para generar credenciales</h6>
            <form method="GET" action="{{ route('secretaria.credenciales') }}" class="row g-3 align-items-center">
                <div class="col-12 col-md-6 col-lg-5">
                    <select name="course_id" class="form-select form-select-lg rounded-pill" onchange="this.form.submit()">
                        <option value="">-- Elige un grado / sección --</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->full_name }} — {{ $c->level }} ({{ $c->students_count }} estudiantes)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-7">
                    <span class="text-muted small">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        Al elegir un grupo podrás imprimir todas las credenciales juntas en hojas tamaño carta listas para recortar o descargar la de cada alumno por separado.
                    </span>
                </div>
            </form>
        </div>
    </div>

    @if($selectedCourse)
        <!-- Credential Preview & List -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">{{ $selectedCourse->full_name }} — {{ $selectedCourse->level }}</h5>
                    <span class="text-muted small">Total de alumnos activos: {{ $students->count() }}</span>
                </div>
                @if($students->isNotEmpty())
                    <div class="dropdown">
                        <button class="btn btn-outline-success btn-sm rounded-pill px-3 dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-file-earmark-pdf"></i> Descargar PDF Lote
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2" style="min-width:280px;">
                            <li>
                                <a class="dropdown-item py-2 px-3 rounded-2" href="{{ route('secretaria.credenciales.grupo', ['course' => $selectedCourse->id, 'tipo' => 'frente_reverso']) }}">
                                    <i class="bi bi-layout-split text-success me-2"></i> Frente + Reverso (Plegable/Enmicar)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 px-3 rounded-2" href="{{ route('secretaria.credenciales.grupo', ['course' => $selectedCourse->id, 'tipo' => 'duplex']) }}">
                                    <i class="bi bi-arrow-repeat text-primary me-2"></i> Dúplex (Doble Cara)
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>

            <div class="card-body p-4 bg-light">
                <div class="row g-4">
                    @forelse($students as $st)
                        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                                <!-- Mini Credential Header -->
                                <div class="bg-dark text-white p-2 d-flex align-items-center gap-2 border-bottom border-success border-2">
                                    <i class="bi bi-mortarboard-fill text-success"></i>
                                    <div class="text-truncate small fw-bold">{{ $setting->school_name }}</div>
                                </div>
                                
                                <div class="card-body p-3 text-center">
                                    @if($st->photo_url)
                                        <img src="{{ $st->photo_url }}" class="rounded-3 mb-2 shadow-sm border border-2 border-success" style="width:70px;height:80px;object-fit:cover;">
                                    @else
                                        <div class="rounded-3 bg-secondary-subtle text-secondary mx-auto mb-2 d-flex align-items-center justify-content-center fw-bold border border-2 border-success" style="width:70px;height:80px;font-size:24px;">
                                            {{ $st->initials }}
                                        </div>
                                    @endif
                                    
                                    <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $st->full_name }}</h6>
                                    <span class="badge bg-success-subtle text-success small mb-2">ESTUDIANTE</span>
                                    
                                    <div class="bg-light p-2 rounded-3 text-start small mb-3">
                                        <div class="text-muted" style="font-size:11px;">Matrícula: <strong class="text-dark">{{ $st->code }}</strong></div>
                                        <div class="text-muted text-truncate" style="font-size:11px;">CURP: <strong class="text-dark">{{ $st->curp ?? 'N/D' }}</strong></div>
                                        <div class="text-muted" style="font-size:11px;">Ciclo: <strong class="text-dark">{{ $setting->academic_year }}</strong></div>
                                    </div>

                                    <a href="{{ route('secretaria.credencial.descargar', $st->id) }}" class="btn btn-outline-primary btn-sm w-100 rounded-pill">
                                        <i class="bi bi-download me-1"></i> Descargar PVC
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                            Este grupo no tiene estudiantes activos registrados actualmente.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="bi bi-person-badge fs-1 text-primary mb-3"></i>
            <h5 class="fw-bold text-dark">Selecciona un grado para comenzar</h5>
            <p class="text-muted small mb-0">Podrás visualizar las credenciales de todos los estudiantes y descargarlas individualmente o en planilla completa.</p>
        </div>
    @endif
</div>
@endsection
