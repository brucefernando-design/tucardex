@extends('layouts.app')
@section('title', 'Captura Rápida de Calificaciones')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>Planilla Rápida de Calificaciones</h1>
        <div class="breadcrumb-mini">Captura ágil tipo hoja de cálculo con navegación por teclado y cálculo de promedios en vivo</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('grades.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver a Calificaciones</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET" action="{{ route('grades.batch') }}" id="filterForm">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Grado / Grupo</label>
                <select name="course_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Seleccione grupo...</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" @selected($courseId==$c->id)>{{ $c->name }} "{{ $c->section }}" ({{ $c->level }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Materia / Asignatura</label>
                <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Seleccione materia...</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" @selected($subjectId==$s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Periodo de Evaluación</label>
                <select name="period" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($periods as $p)
                        <option value="{{ $p }}" @selected($period==$p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tipo de Calificación</label>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($types as $t)
                        <option value="{{ $t }}" @selected($type==$t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

@if($courseId && $subjectId)
    <form action="{{ route('grades.batchStore') }}" method="POST" id="batchGradesForm">
        @csrf
        <input type="hidden" name="course_id" value="{{ $courseId }}">
        <input type="hidden" name="subject_id" value="{{ $subjectId }}">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="hidden" name="type" value="{{ $type }}">

        <!-- Barra de Estadísticas y Productividad en Vivo -->
        <div class="card mb-3 border-primary shadow-sm">
            <div class="card-body py-2 px-3">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">
                            <i class="bi bi-keyboard me-1"></i> Modo Excel Activo
                        </span>
                    </div>
                    <div class="col-md">
                        <div class="d-flex flex-wrap gap-4 align-items-center text-secondary small">
                            <div>
                                <span class="text-muted">Total Alumnos:</span>
                                <strong class="text-dark">{{ $students->count() }}</strong>
                            </div>
                            <div>
                                <span class="text-muted">Capturados:</span>
                                <strong id="stat-evaluated" class="text-dark">0</strong> / {{ $students->count() }}
                            </div>
                            <div>
                                <span class="text-muted">Promedio Grupal:</span>
                                <strong id="stat-average" class="fs-6 text-primary">-</strong>
                            </div>
                            <div>
                                <span class="text-muted">Aprobados (≥ 6.0):</span>
                                <strong id="stat-passed" class="text-success">0</strong>
                                <span id="stat-passed-pct" class="text-muted">(0%)</span>
                            </div>
                            <div>
                                <span class="text-muted">Reprobados (< 6.0):</span>
                                <strong id="stat-failed" class="text-danger">0</strong>
                                <span id="stat-failed-pct" class="text-muted">(0%)</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <!-- Relleno Rápido -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Asignar nota a casillas vacías">
                                <i class="bi bi-lightning-charge"></i> Rellenar vacíos
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><h6 class="dropdown-header">Rellenar pendientes con:</h6></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="quickFill(10)">Calificación 10.0</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="quickFill(9)">Calificación 9.0</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="quickFill(8)">Calificación 8.0</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="quickFill(7)">Calificación 7.0</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="quickFill(6)">Calificación 6.0 (Mínima)</a></li>
                            </ul>
                        </div>
                        <button type="submit" class="btn btn-sm btn-brand btn-icon shadow-sm" id="btnSaveTop">
                            <i class="bi bi-save"></i> Guardar todas <kbd class="bg-dark text-white ms-1 px-1 small" style="font-size:10px">Ctrl+S</kbd>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light py-1 px-3 border-top small text-muted d-flex justify-content-between align-items-center">
                <span><i class="bi bi-info-circle me-1 text-primary"></i> <strong>Atajos:</strong> Pulsa <kbd>Enter</kbd> o <kbd>↓</kbd> para ir al siguiente alumno. <kbd>Shift+Enter</kbd> o <kbd>↑</kbd> para regresar. Texto se autoselecciona al enfocar.</span>
                <span>Escala oficial SEP: <strong>5.0 a 10.0</strong></span>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="gradesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" width="50">#</th>
                                <th width="140">Matrícula</th>
                                <th>Nombre Completo del Estudiante</th>
                                <th width="180" class="text-center">Calificación (5.0 - 10.0)</th>
                                <th width="150" class="text-center">Estatus</th>
                                <th width="100" class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($students as $i => $s)
                            @php
                                $val = optional($existing->get($s->id))->score;
                                $displayVal = $val !== null ? rtrim(rtrim(number_format($val, 2, '.', ''), '0'), '.') : '';
                            @endphp
                            <tr id="row-{{ $s->id }}" class="student-row">
                                <td class="ps-3 text-muted fw-bold">{{ $i + 1 }}</td>
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $s->enrollment_code ?? 'S/M' }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar-sm me-2 bg-primary-subtle text-primary fw-bold">
                                            {{ mb_substr($s->first_name, 0, 1) }}{{ mb_substr($s->last_name, 0, 1) }}
                                        </span>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $s->full_name }}</div>
                                            <div class="text-muted small">{{ $s->curp ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-block position-relative" style="width: 140px;">
                                        <input type="number"
                                               name="scores[{{ $s->id }}]"
                                               value="{{ $displayVal }}"
                                               min="5"
                                               max="10"
                                               step="0.1"
                                               autocomplete="off"
                                               class="form-control form-control-sm text-center fw-bold score-input"
                                               placeholder="5.0 - 10.0"
                                               data-index="{{ $i }}"
                                               id="score_{{ $i }}">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="sit-badge badge {{ $displayVal !== '' ? ($displayVal >= 6.0 ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger') : 'bg-light text-muted border' }}">
                                        @if($displayVal === '')
                                            <i class="bi bi-dash-circle me-1"></i>Pendiente
                                        @elseif($displayVal >= 6.0)
                                            <i class="bi bi-check-circle me-1"></i>Aprobado
                                        @else
                                            <i class="bi bi-x-circle me-1"></i>Reprobado
                                        @endif
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-link text-muted p-0" title="Borrar nota" onclick="clearScore('score_{{ $i }}')">
                                        <i class="bi bi-eraser"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state py-5">
                                        <i class="bi bi-people fs-1 text-muted"></i>
                                        <p class="mt-2 text-muted">Este grado/grupo no tiene alumnos asignados actualmente.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($students->count() > 0)
                <div class="card-footer bg-white py-3 px-4 d-flex justify-content-between align-items-center border-top">
                    <span class="small text-muted">
                        Presiona <strong class="text-dark">Guardar todas</strong> para consolidar las calificaciones en el Kardex y Boletas.
                    </span>
                    <button type="submit" class="btn btn-brand btn-icon shadow">
                        <i class="bi bi-save"></i> Guardar todas las calificaciones
                    </button>
                </div>
            @endif
        </div>
    </form>
@else
    <div class="card shadow-sm">
        <div class="card-body empty-state py-5 text-center">
            <i class="bi bi-file-earmark-spreadsheet fs-1 text-primary mb-3"></i>
            <h4 class="fw-bold">Selecciona los parámetros de captura</h4>
            <p class="text-muted" style="max-width:500px;margin:0 auto">
                Elige el grado/grupo, la materia, el periodo y tipo de evaluación en la barra superior para cargar la planilla de alumnos y capturar sus notas rápidamente.
            </p>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = Array.from(document.querySelectorAll('.score-input'));

    function updateRowStatus(inp) {
        const tr = inp.closest('tr');
        const badge = tr.querySelector('.sit-badge');
        const val = parseFloat(inp.value);

        if (inp.value.trim() === '' || isNaN(val)) {
            badge.className = 'sit-badge badge bg-light text-muted border';
            badge.innerHTML = '<i class="bi bi-dash-circle me-1"></i>Pendiente';
            inp.classList.remove('is-invalid', 'border-success', 'border-danger');
        } else if (val < 5.0 || val > 10.0) {
            badge.className = 'sit-badge badge bg-warning-subtle text-warning-emphasis border border-warning';
            badge.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Inválido (5-10)';
            inp.classList.add('is-invalid');
        } else if (val >= 6.0) {
            badge.className = 'sit-badge badge bg-success-subtle text-success border border-success';
            badge.innerHTML = '<i class="bi bi-check-circle me-1"></i>Aprobado';
            inp.classList.remove('is-invalid');
            inp.classList.add('border-success');
        } else {
            badge.className = 'sit-badge badge bg-danger-subtle text-danger border border-danger';
            badge.innerHTML = '<i class="bi bi-x-circle me-1"></i>Reprobado';
            inp.classList.remove('is-invalid');
            inp.classList.add('border-danger');
        }
    }

    function recalculateStats() {
        let evaluated = 0;
        let sum = 0;
        let passed = 0;
        let failed = 0;

        inputs.forEach(inp => {
            const val = parseFloat(inp.value);
            if (!isNaN(val) && val >= 5.0 && val <= 10.0) {
                evaluated++;
                sum += val;
                if (val >= 6.0) {
                    passed++;
                } else {
                    failed++;
                }
            }
        });

        const total = inputs.length;
        const avg = evaluated > 0 ? (sum / evaluated).toFixed(2) : '-';
        const passedPct = evaluated > 0 ? Math.round((passed / evaluated) * 100) : 0;
        const failedPct = evaluated > 0 ? Math.round((failed / evaluated) * 100) : 0;

        const elEvaluated = document.getElementById('stat-evaluated');
        const elAverage = document.getElementById('stat-average');
        const elPassed = document.getElementById('stat-passed');
        const elPassedPct = document.getElementById('stat-passed-pct');
        const elFailed = document.getElementById('stat-failed');
        const elFailedPct = document.getElementById('stat-failed-pct');

        if (elEvaluated) elEvaluated.textContent = evaluated;
        if (elAverage) elAverage.textContent = avg;
        if (elPassed) elPassed.textContent = passed;
        if (elPassedPct) elPassedPct.textContent = `(${passedPct}%)`;
        if (elFailed) elFailed.textContent = failed;
        if (elFailedPct) elFailedPct.textContent = `(${failedPct}%)`;
    }

    // Attach event listeners to all inputs
    inputs.forEach((inp, idx) => {
        // Select all text upon focus for instant overwriting
        inp.addEventListener('focus', function () {
            this.select();
            this.closest('tr').classList.add('table-primary');
        });

        inp.addEventListener('blur', function () {
            this.closest('tr').classList.remove('table-primary');
        });

        // Dynamic change & calculation
        inp.addEventListener('input', function () {
            updateRowStatus(this);
            recalculateStats();
        });

        // Keyboard navigation (Excel style)
        inp.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (e.shiftKey) {
                    // Shift + Enter: Move up
                    if (idx > 0) {
                        inputs[idx - 1].focus();
                        inputs[idx - 1].select();
                    }
                } else {
                    // Enter: Move down
                    if (idx < inputs.length - 1) {
                        inputs[idx + 1].focus();
                        inputs[idx + 1].select();
                    }
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (idx < inputs.length - 1) {
                    inputs[idx + 1].focus();
                    inputs[idx + 1].select();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (idx > 0) {
                    inputs[idx - 1].focus();
                    inputs[idx - 1].select();
                }
            }
        });
    });

    // Global shortcut Ctrl+S / Cmd+S to submit form quickly
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            const form = document.getElementById('batchGradesForm');
            if (form) {
                e.preventDefault();
                form.submit();
            }
        }
    });

    // Initial calculation on page load
    recalculateStats();
});

// Helper functions accessible in window
window.quickFill = function (grade) {
    const inputs = document.querySelectorAll('.score-input');
    let filled = 0;
    inputs.forEach(inp => {
        if (inp.value.trim() === '') {
            inp.value = Number(grade).toFixed(1);
            inp.dispatchEvent(new Event('input', { bubbles: true }));
            filled++;
        }
    });
};

window.clearScore = function (id) {
    const inp = document.getElementById(id);
    if (inp) {
        inp.value = '';
        inp.dispatchEvent(new Event('input', { bubbles: true }));
        inp.focus();
    }
};
</script>
@endpush
