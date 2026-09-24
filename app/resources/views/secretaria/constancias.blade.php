@extends('layouts.app')

@section('title', 'Emisión de Constancias y Certificados')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('secretaria.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle p-1" title="Volver a Secretaría">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0 text-dark">Emisión y Personalización de Constancias</h3>
            </div>
            <p class="text-muted small mb-0">Genera constancias oficiales con folio único consecutivo, código QR de autenticidad y membrete institucional.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Student Selector & Info -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-search text-primary me-2"></i>1. Seleccionar Estudiante</h6>
                    
                    <form method="GET" action="{{ route('secretaria.constancias') }}">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Buscar por nombre o matrícula</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control rounded-start-pill" placeholder="Escribe para buscar..." value="{{ request('search') }}">
                                <button class="btn btn-primary rounded-end-pill px-3"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </form>

                    @if(request('search'))
                        @php
                            $searchResults = \App\Models\Student::where('status', 'activo')
                                ->where(function($q){
                                    $s = request('search');
                                    $q->where('first_name', 'like', "%{$s}%")
                                      ->orWhere('last_name', 'like', "%{$s}%")
                                      ->orWhere('code', 'like', "%{$s}%")
                                      ->orWhere('curp', 'like', "%{$s}%");
                                })->limit(8)->get();
                        @endphp
                        <div class="list-group list-group-flush mb-3 border rounded-3 overflow-hidden">
                            @forelse($searchResults as $res)
                                <a href="{{ route('secretaria.constancias', ['student_id' => $res->id]) }}" class="list-group-item list-group-item-action p-2 {{ optional($student)->id == $res->id ? 'active' : '' }}">
                                    <div class="fw-bold small">{{ $res->full_name }}</div>
                                    <div class="text-muted small" style="font-size:11px;">Mat: {{ $res->code }} · {{ optional($res->course)->name }}</div>
                                </a>
                            @empty
                                <div class="p-3 text-center text-muted small">No se encontraron resultados.</div>
                            @endforelse
                        </div>
                    @endif

                    @if($student)
                        <div class="bg-light p-3 rounded-4 mt-3">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                @if($student->photo_url)
                                    <img src="{{ $student->photo_url }}" class="rounded-circle" style="width:48px;height:48px;object-fit:cover;">
                                @else
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold" style="width:48px;height:48px;font-size:16px;">
                                        {{ $student->initials }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $student->full_name }}</div>
                                    <div class="text-muted small">Mat: {{ $student->code }}</div>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="small">
                                <div class="text-muted">CURP: <strong class="text-dark">{{ $student->curp ?? 'N/D' }}</strong></div>
                                <div class="text-muted">Grado/Grupo: <strong class="text-dark">{{ optional($student->course)->name }} "{{ optional($student->course)->section }}"</strong></div>
                                <div class="text-muted">Tutor: <strong class="text-dark">{{ $student->guardian_name ?? 'N/D' }}</strong></div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info border-0 rounded-3 small mt-3">
                            <i class="bi bi-info-circle me-1"></i> Por favor busca y selecciona un alumno para habilitar los formularios de emisión.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Document Types & Forms -->
        <div class="col-12 col-lg-8">
            @if($student)
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 p-3 px-4">
                        <ul class="nav nav-pills nav-fill gap-2 p-1 bg-light rounded-pill" id="docTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill fw-medium small" id="tab-estudios" data-bs-toggle="tab" data-bs-target="#content-estudios" type="button">
                                    <i class="bi bi-file-earmark-check me-1"></i> Constancia Estudios
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-medium small" id="tab-kardex" data-bs-toggle="tab" data-bs-target="#content-kardex" type="button">
                                    <i class="bi bi-journal-bookmark me-1"></i> Kárdex / Calificaciones
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-medium small" id="tab-conducta" data-bs-toggle="tab" data-bs-target="#content-conducta" type="button">
                                    <i class="bi bi-shield-check me-1"></i> Buena Conducta
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-medium small" id="tab-noadeudo" data-bs-toggle="tab" data-bs-target="#content-noadeudo" type="button">
                                    <i class="bi bi-cash-coin me-1"></i> No Adeudo
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-medium small" id="tab-ficha" data-bs-toggle="tab" data-bs-target="#content-ficha" type="button">
                                    <i class="bi bi-file-person me-1"></i> Ficha Matrícula
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-4">
                        <div class="tab-content" id="docTabsContent">
                            
                            <!-- TAB 1: Constancia de Estudios -->
                            <div class="tab-pane fade show active" id="content-estudios" role="tabpanel">
                                <h6 class="fw-bold text-dark mb-3">Constancia de Estudios Oficial</h6>
                                <form method="GET" action="{{ route('secretaria.constancia.descargar', $student->id) }}" target="_blank">
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Dirigido a:</label>
                                        <input type="text" name="dirigido_a" class="form-control" value="A QUIEN CORRESPONDA" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Motivo o fin de la constancia:</label>
                                        <input type="text" name="motivo" class="form-control" value="los fines legales y administrativos que al interesado convengan.">
                                        <div class="form-text">Ejemplo: "trámite de beca escolar", "afiliación a seguridad social", "trámite de pasaporte", etc.</div>
                                    </div>
                                    <div class="form-check form-switch mb-4">
                                        <input class="form-check-input" type="checkbox" name="incluir_promedio" value="1" id="switchPromedio">
                                        <label class="form-check-label small" for="switchPromedio">Incluir promedio general obtenido hasta la fecha</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Generar y Descargar PDF
                                    </button>
                                </form>
                            </div>

                            <!-- TAB 2: Kárdex Académico -->
                            <div class="tab-pane fade" id="content-kardex" role="tabpanel">
                                <h6 class="fw-bold text-dark mb-3">Historial Académico / Kárdex Oficial</h6>
                                <p class="text-muted small">Genera el desglose completo de materias, calificaciones trimestrales/parciales, promedio acumulado y sellos oficiales.</p>
                                <a href="{{ route('secretaria.kardex.descargar', $student->id) }}" target="_blank" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Descargar Kárdex en PDF
                                </a>
                            </div>

                            <!-- TAB 3: Carta de Buena Conducta -->
                            <div class="tab-pane fade" id="content-conducta" role="tabpanel">
                                <h6 class="fw-bold text-dark mb-3">Carta Oficial de Buena Conducta</h6>
                                <form method="GET" action="{{ route('secretaria.buena-conducta.descargar', $student->id) }}" target="_blank">
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Dirigido a:</label>
                                        <input type="text" name="dirigido_a" class="form-control" value="A QUIEN CORRESPONDA" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Texto de Observación / Desempeño:</label>
                                        <textarea name="observaciones" class="form-control" rows="3">Durante su permanencia en esta institución ha demostrado un comportamiento ejemplar, respetando las normas y valores de nuestra comunidad educativa.</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Generar y Descargar PDF
                                    </button>
                                </form>
                            </div>

                            <!-- TAB 4: Constancia de No Adeudo -->
                            <div class="tab-pane fade" id="content-noadeudo" role="tabpanel">
                                <h6 class="fw-bold text-dark mb-3">Constancia de Solvencia / No Adeudo</h6>
                                <p class="text-muted small">Certifica que el alumno se encuentra al corriente de todas sus colegiaturas y obligaciones económicas con el colegio.</p>
                                <form method="GET" action="{{ route('secretaria.no-adeudo.descargar', $student->id) }}" target="_blank">
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Dirigido a:</label>
                                        <input type="text" name="dirigido_a" class="form-control" value="A QUIEN CORRESPONDA" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Generar y Descargar PDF
                                    </button>
                                </form>
                            </div>

                            <!-- TAB 5: Ficha de Matrícula -->
                            <div class="tab-pane fade" id="content-ficha" role="tabpanel">
                                <h6 class="fw-bold text-dark mb-3">Cédula Oficial y Ficha de Inscripción</h6>
                                <p class="text-muted small">Expediente completo con datos del estudiante, tutor legal, grado adscrito y formato de firmas de conformidad para archivo físico escolar.</p>
                                <a href="{{ route('secretaria.ficha.descargar', $student->id) }}" target="_blank" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Descargar Ficha en PDF
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <i class="bi bi-file-earmark-text fs-1 text-muted mb-3"></i>
                    <h5 class="fw-bold text-dark">Ningún estudiante seleccionado</h5>
                    <p class="text-muted small mb-0">Usa el buscador del panel izquierdo para seleccionar al alumno y emitir cualquier trámite oficial.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
