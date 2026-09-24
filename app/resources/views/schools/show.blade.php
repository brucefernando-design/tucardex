@extends('layouts.app')
@section('title', 'Gestionar ' . $school->name)

@section('content')
<div class="page-head">
    <div>
        <h1>{{ $school->name }}</h1>
        <div class="breadcrumb-mini">Colegios / {{ $school->slug }} · Plan <strong class="text-capitalize">{{ $school->plan }}</strong></div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('schools.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
        
        {{-- Botón de Suplantación / Impersonate --}}
        <form action="{{ route('schools.impersonate', $school) }}" method="POST" onsubmit="return confirm('¿Deseas ingresar al colegio {{ $school->name }} en modo soporte técnico? Podrás salir en cualquier momento con la barra superior.');">
            @csrf
            <button type="submit" class="btn btn-primary btn-icon shadow-sm">
                <i class="bi bi-box-arrow-in-right"></i> Entrar como Director a este Colegio
            </button>
        </form>
        
        {{-- Botón de Pausar / Reactivar Colegio --}}
        <form action="{{ route('schools.toggle_status', $school) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas cambiar el estado de este colegio?');">
            @csrf
            @if($school->status === 'activo')
                <button type="submit" class="btn btn-outline-danger btn-icon">
                    <i class="bi bi-pause-circle"></i> Suspender Colegio
                </button>
            @else
                <button type="submit" class="btn btn-success btn-icon">
                    <i class="bi bi-play-circle"></i> Reactivar Colegio
                </button>
            @endif
        </form>
    </div>
</div>

@if($school->status === 'suspendido')
<div class="alert alert-danger shadow-sm mb-4">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-octagon-fill fs-4"></i>
        <div>
            <strong>Colegio Suspendido / Pausado:</strong> Ningún usuario o padre de familia de esta institución puede ingresar al portal hasta que sea reactivado.
        </div>
    </div>
</div>
@endif

{{-- Métricas del Colegio --}}
<div class="stats-row mb-4">
    <div class="stat-card bg-teal">
        <div class="label">Alumnos Activos</div>
        <div class="value">{{ $stats['students'] }}</div>
        <i class="bi bi-people icon"></i>
    </div>
    <div class="stat-card bg-green">
        <div class="label">Docentes</div>
        <div class="value">{{ $stats['teachers'] }}</div>
        <i class="bi bi-person-badge icon"></i>
    </div>
    <div class="stat-card bg-blue">
        <div class="label">Usuarios en Sistema</div>
        <div class="value">{{ $stats['users'] }}</div>
        <i class="bi bi-person-gear icon"></i>
    </div>
    <div class="stat-card bg-dark">
        <div class="label">Colegiaturas Cobradas</div>
        <div class="value">${{ number_format($stats['income'], 2) }} MXN</div>
        <i class="bi bi-cash icon"></i>
    </div>
</div>

<div class="row g-4">
    {{-- Columna Izquierda: Configuración General y Suscripción SaaS --}}
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="title fw-bold"><i class="bi bi-sliders text-success me-2"></i> Configuración y Tarifas de Suscripción</span>
                <span class="badge {{ $school->status=='activo' ? 'bg-success':'bg-danger' }} text-uppercase">{{ $school->status }}</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('schools.update', $school) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-bold text-dark mb-3">Datos Generales</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Nombre del Colegio</label>
                            <input name="name" value="{{ old('name', $school->name) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Plan Activo</label>
                            <select name="plan" class="form-select">
                                @foreach(\App\Models\School::PLANS as $k=>$v)
                                    <option value="{{ $k }}" @selected(old('plan', $school->plan)==$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Teléfono de Contacto</label>
                            <input name="phone" value="{{ old('phone', $school->phone) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Correo de Contacto</label>
                            <input name="email" value="{{ old('email', $school->email) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Estado del Servicio</label>
                            <select name="status" class="form-select">
                                <option value="activo" @selected($school->status=='activo')>Activo (Habilitado)</option>
                                <option value="suspendido" @selected($school->status=='suspendido')>Suspendido (Bloqueado)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Estado de Suscripción</label>
                            <select name="subscription_status" class="form-select">
                                <option value="active" @selected($school->subscription_status=='active')>Al Corriente (Activa)</option>
                                <option value="trial" @selected($school->subscription_status=='trial')>Periodo de Prueba</option>
                                <option value="past_due" @selected($school->subscription_status=='past_due')>Pago Atrasado</option>
                                <option value="cancelled" @selected($school->subscription_status=='cancelled')>Cancelada</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-top pt-3 mb-3">Esquema de Cobro SaaS (Tarifas a este Colegio)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Precio Mensual por Alumno ($ MXN)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.50" name="price_per_student" value="{{ old('price_per_student', $school->price_per_student ?: 15.00) }}" class="form-control" required>
                                <span class="input-group-text">MXN</span>
                            </div>
                            <div class="form-text">Cobro por alumno activo al mes.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Cuota Mínima Garantizada ($ MXN)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="100" name="minimum_monthly_fee" value="{{ old('minimum_monthly_fee', $school->minimum_monthly_fee ?: 0) }}" class="form-control">
                                <span class="input-group-text">MXN</span>
                            </div>
                            <div class="form-text">Mínimo mensual a cobrar si tienen pocos alumnos.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Próxima Fecha de Renovación</label>
                            <input type="date" name="billing_renews_at" value="{{ old('billing_renews_at', optional($school->billing_renews_at)->format('Y-m-d')) }}" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Vencimiento Periodo de Prueba</label>
                            <input type="date" name="trial_ends_at" value="{{ old('trial_ends_at', optional($school->trial_ends_at)->format('Y-m-d')) }}" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-brand btn-icon shadow-sm">
                        <i class="bi bi-check-lg"></i> Guardar Cambios y Tarifas
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Columna Derecha: Resumen Financiero y Administradores --}}
    <div class="col-lg-5">
        {{-- Tarjeta de Suscripción Calculada --}}
        @php $sub = $school->calculateMonthlySubscription(); @endphp
        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px; background:linear-gradient(135deg, #f0fdf4, #dcfce7); border:1.5px solid #bbf7d0 !important;">
            <div class="card-body p-4">
                <span class="small text-uppercase fw-bold text-success tracking-wider d-block mb-1">
                    <i class="bi bi-receipt me-1"></i> Facturación Mensual Estimada
                </span>
                <h2 class="fw-bold text-dark mb-0">${{ number_format($sub['total'], 2) }} <span class="fs-6 text-muted fw-normal">MXN / mes</span></h2>
                <hr class="my-3">
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Alumnos Activos:</span>
                    <strong>{{ $sub['active_students'] }} alumnos</strong>
                </div>
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Tarifa acordada:</span>
                    <strong>${{ number_format($sub['unit_price'], 2) }} MXN / alumno</strong>
                </div>
                @if($sub['applied_minimum'])
                <div class="alert alert-warning py-1 px-2 small mt-2 mb-0">
                    <i class="bi bi-info-circle me-1"></i> Aplica cuota mínima mensual de ${{ number_format($sub['minimum_fee'], 2) }} MXN
                </div>
                @endif
            </div>
        </div>

        {{-- Cuentas de Administradores con Reseteo de Contraseña --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius:14px;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="title fw-bold"><i class="bi bi-shield-lock-fill text-primary me-2"></i> Administradores del Colegio</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <tbody>
                        @forelse($admins as $a)
                            <tr>
                                <td class="ps-3 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:36px;height:36px;border-radius:8px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:14px;">
                                            {{ $a->initials() }}
                                        </div>
                                        <div>
                                            <strong class="d-block text-dark">{{ $a->name }}</strong>
                                            <span class="small text-muted">{{ $a->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalPass{{ $a->id }}" title="Cambiar contraseña de este administrador">
                                        <i class="bi bi-key"></i> Clave
                                    </button>

                                    <!-- Modal Reset Password -->
                                    <div class="modal fade text-start" id="modalPass{{ $a->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form action="{{ route('schools.reset_admin_password', $school) }}" method="POST" class="modal-content">
                                                @csrf
                                                <input type="hidden" name="admin_id" value="{{ $a->id }}">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold"><i class="bi bi-key text-primary me-2"></i> Cambiar Contraseña para {{ $a->name }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="small text-muted">Ingresa la nueva contraseña para el administrador del colegio. El cambio surtirá efecto de inmediato.</p>
                                                    <label class="form-label small fw-bold">Nueva Contraseña:</label>
                                                    <input type="text" name="new_password" class="form-control font-monospace" value="Colegio{{ rand(100,999) }}*" required minlength="6">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="text-center py-4 text-muted">Sin administradores asignados.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
