@extends('layouts.app')
@section('title', 'Admisiones y Fichas de Preinscripción')

@section('content')
<div class="page-head">
    <div>
        <h1>Admisiones y Preinscripciones</h1>
        <div class="breadcrumb-mini">Gestión de aspirantes y registro de fichas en línea</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admissions.public_form', $school->slug ?? 'colegio-san-martin') }}" target="_blank" class="btn btn-brand btn-icon shadow-sm">
            <i class="bi bi-box-arrow-up-right"></i> Ver Portal Público de Fichas
        </a>
    </div>
</div>

{{-- Banner con enlace para compartir a padres en redes / WhatsApp --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px; background:linear-gradient(135deg, #f0fdf4, #dcfce7); border:1.5px solid #bbf7d0 !important;">
    <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div style="width:44px;height:44px;border-radius:10px;background:#16a34a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;">
                <i class="bi bi-link-45deg"></i>
            </div>
            <div>
                <strong class="d-block text-dark">Enlace Público de Admisiones para Padres de Familia</strong>
                <span class="small text-muted">Comparte este enlace por WhatsApp, Facebook o en el sitio web de la escuela para que los aspirantes se preinscriban.</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="publicLinkInput" class="form-control form-control-sm bg-white font-monospace" value="{{ route('admissions.public_form', $school->slug ?? 'colegio') }}" readonly style="max-width:340px;">
            <button type="button" class="btn btn-sm btn-dark" onclick="copyPublicLink()">
                <i class="bi bi-copy me-1"></i> Copiar Enlace
            </button>
        </div>
    </div>
</div>

{{-- Tarjetas de Métricas --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px; background:#f8fafc; border-left:4px solid #64748b !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Aspirantes</span>
                    <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $stats['total'] }}</h3>
                    <small class="text-secondary">Fichas registradas</small>
                </div>
                <div style="width:44px;height:44px;border-radius:10px;background:#e2e8f0;color:#475569;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="bi bi-folder2-open"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px; background:#fefce8; border-left:4px solid #ca8a04 !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Por Revisar</span>
                    <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $stats['pendientes'] }}</h3>
                    <small class="text-warning-emphasis fw-bold">Pendientes de evaluación</small>
                </div>
                <div style="width:44px;height:44px;border-radius:10px;background:#fef9c3;color:#ca8a04;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px; background:#eff6ff; border-left:4px solid #2563eb !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Aceptadas</span>
                    <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $stats['aceptadas'] }}</h3>
                    <small class="text-primary fw-bold">Listas para matricular</small>
                </div>
                <div style="width:44px;height:44px;border-radius:10px;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px; background:#f0fdf4; border-left:4px solid #16a34a !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Matriculadas</span>
                    <h3 class="mb-0 fw-bold mt-1 text-dark">{{ $stats['matriculadas'] }}</h3>
                    <small class="text-success fw-bold">Alumnos activos</small>
                </div>
                <div style="width:44px;height:44px;border-radius:10px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="bi bi-mortarboard"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:14px;">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-6">
                <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar por Folio, Nombre del aspirante, CURP o Tutor...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" @selected(request('status')=='pendiente')>Pendiente</option>
                    <option value="en_revision" @selected(request('status')=='en_revision')>En Revisión</option>
                    <option value="aceptada" @selected(request('status')=='aceptada')>Aceptada</option>
                    <option value="rechazada" @selected(request('status')=='rechazada')>Rechazada</option>
                    <option value="matriculada" @selected(request('status')=='matriculada')>Matriculada</option>
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-outline-secondary btn-icon"><i class="bi bi-search"></i> Filtrar</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Folio</th>
                        <th>Aspirante</th>
                        <th>Grado</th>
                        <th>Tutor / Contacto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($admissions as $adm)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('secretaria.admisiones.show', $adm) }}" class="font-monospace fw-bold text-primary text-decoration-none">
                                {{ $adm->folio }}
                            </a>
                        </td>
                        <td>
                            <strong>{{ $adm->full_name }}</strong>
                            <div class="small text-muted font-monospace">{{ $adm->curp ?? 'Sin CURP' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ optional($adm->course)->name ?? 'General' }}</span>
                        </td>
                        <td>
                            <div>{{ $adm->guardian_name }}</div>
                            <div class="d-flex align-items-center gap-2 small">
                                <a href="https://wa.me/52{{ preg_replace('/\D/', '', $adm->guardian_phone) }}" target="_blank" class="text-success text-decoration-none">
                                    <i class="bi bi-whatsapp"></i> {{ $adm->guardian_phone }}
                                </a>
                            </div>
                        </td>
                        <td>
                            {!! $adm->statusBadge() !!}
                        </td>
                        <td class="small text-muted">
                            {{ $adm->created_at->format('d/m/Y') }}
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('secretaria.admisiones.show', $adm) }}" class="btn btn-sm btn-light border" title="Ver Expediente Completo">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                @if($adm->status !== 'matriculada')
                                    <a href="{{ route('secretaria.admisiones.show', $adm) }}#matricular" class="btn btn-sm btn-success shadow-sm" title="Matricular como Alumno">
                                        <i class="bi bi-mortarboard"></i> Matricular
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No hay solicitudes de preinscripción registradas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $admissions->links() }}
        </div>
    </div>
</div>

<script>
function copyPublicLink() {
    const input = document.getElementById('publicLinkInput');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        alert('Enlace público de admisiones copiado al portapapeles: ' + input.value);
    });
}
</script>
@endsection
