@extends('layouts.app')
@section('title', 'Configuración')

@section('content')
<div class="page-head"><div><h1>Configuración</h1><div class="breadcrumb-mini">Parámetros del sistema, gestión académica y roles</div></div></div>

<div class="grid-2">
    <div class="card card-accent">
        <div class="card-header"><span class="title"><i class="bi bi-building"></i> Datos de la institución y ciclo escolar</span></div>
        <div class="card-body">
            <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
                <div class="d-flex align-items-center gap-3 mb-3 p-3" style="background:#f7faf9;border-radius:12px">
                    <div style="width:64px;height:64px;border-radius:14px;overflow:hidden;background:linear-gradient(135deg,var(--brand-2),var(--brand-3));display:flex;align-items:center;justify-content:center;font-size:30px;color:#fff;flex-shrink:0">
                        @if($setting->logo_url)<img src="{{ $setting->logo_url }}" alt="logo" style="width:100%;height:100%;object-fit:cover">@else🎓@endif
                    </div>
                    <div class="flex-grow-1">
                        <label class="form-label">Logo del colegio</label>
                        <input type="file" name="logo" accept="image/*" class="form-control">
                        <div class="form-text">Se mostrará en el menú, login, landing y documentos PDF. JPG/PNG/SVG, máx 2MB.</div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Nombre del colegio</label><input name="school_name" value="{{ old('school_name', $setting->school_name) }}" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Moneda</label><input name="currency" value="{{ old('currency', $setting->currency) }}" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Ciclo Escolar <span class="text-danger">*</span></label><input name="academic_year" value="{{ old('academic_year', $setting->academic_year) }}" class="form-control" required></div>
                    <div class="col-md-4">
                        <label class="form-label">Período activo <span class="text-danger">*</span></label>
                        <select name="active_period" class="form-select">
                            @foreach(['1er Trimestre','2do Trimestre','3er Trimestre','Final'] as $p)
                                <option value="{{ $p }}" @selected(old('active_period', $setting->active_period)==$p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Colegiatura mensual (referencial)</label><input type="number" step="0.01" name="tuition_amount" value="{{ old('tuition_amount', $setting->tuition_amount) }}" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Director(a)</label><input name="director" value="{{ old('director', $setting->director) }}" class="form-control"></div>
                    <div class="col-md-2"><label class="form-label">Cédula Profesional</label><input name="cedula_profesional" value="{{ old('cedula_profesional', $setting->cedula_profesional) }}" class="form-control" placeholder="Ej: 12345678" maxlength="20"><div class="form-text">Cédula profesional del Director(a) (requerida SEP).</div></div>
                    <div class="col-md-6"><label class="form-label">Teléfono</label><input name="phone" value="{{ old('phone', $setting->phone) }}" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Dirección</label><input name="address" value="{{ old('address', $setting->address) }}" class="form-control"></div>
                    <div class="col-12 mt-4 pt-3 border-top">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <h6 class="mb-0 fw-bold"><i class="bi bi-laptop me-1"></i> Cuota de Recuperación "Plataforma Digital"</h6>
                                <small class="text-muted">Permite al colegio recuperar el costo de TuCardex mediante un renglón cobrado a las familias.</small>
                            </div>
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" name="platform_fee_enabled" value="1" id="platformFeeSwitch" @checked(old('platform_fee_enabled', $setting->platform_fee_enabled))>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label">Monto de la cuota ($ MXN)</label>
                                <input type="number" step="0.01" min="0" name="platform_fee_amount" value="{{ old('platform_fee_amount', $setting->platform_fee_amount ?? 30.00) }}" class="form-control" placeholder="30.00">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Concepto en recibo y estado de cuenta</label>
                                <input type="text" name="platform_fee_label" value="{{ old('platform_fee_label', $setting->platform_fee_label ?? 'Plataforma digital / portal familias') }}" class="form-control" placeholder="Plataforma digital / portal familias">
                                <div class="form-text">Aparecerá como un segundo cargo independiente para cada alumno al generar las colegiaturas.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4"><button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar configuración</button></div>
            </form>
        </div>
    </div>
        <div>
        @if(isset($subscription))
        <div class="card mb-3 border border-primary-subtle shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <span class="title"><i class="bi bi-star-fill text-warning me-1"></i> Suscripción TuCardex</span>
                <span class="badge {{ $subscription['status'] === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">
                    {{ ucfirst($subscription['status'] === 'active' ? 'Activa' : $subscription['status']) }}
                </span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-baseline justify-content-between mb-3 pb-2 border-bottom">
                    <div>
                        <div class="small text-muted text-uppercase fw-bold" style="font-size:11px;">Plan Asignado</div>
                        <h4 class="mb-0 text-primary">{{ $subscription['plan_name'] }}</h4>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted text-uppercase fw-bold" style="font-size:11px;">Cuota por Alumno</div>
                        <span class="fs-5 fw-bold">${{ number_format($subscription['unit_price'], 2) }}</span> <span class="small text-muted">MXN/mes</span>
                    </div>
                </div>

                <div class="row g-2 mb-3 text-center">
                    <div class="col-6">
                        <div class="p-2 rounded bg-light border">
                            <small class="text-muted d-block" style="font-size:11px;">Matrícula Activa</small>
                            <strong class="fs-6">{{ $subscription['active_students'] }} alumnos</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded bg-light border">
                            <small class="text-muted d-block" style="font-size:11px;">Piso Mínimo Mensual</small>
                            <strong class="fs-6">${{ number_format($subscription['minimum_fee'], 2) }} MXN</strong>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded mb-3" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Cálculo estimado del mes:</span>
                        @if($subscription['applied_minimum'])
                            <span class="badge bg-warning text-dark border border-warning" style="font-size:10px;">Piso Mínimo Aplicado</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between align-items-baseline mt-1">
                        <span class="small">{{ $subscription['active_students'] }} alumnos × ${{ number_format($subscription['unit_price'], 2) }}</span>
                        <h4 class="mb-0 text-success fw-bold">${{ number_format($subscription['total'], 2) }} <span class="fs-6 fw-normal text-muted">MXN / mes</span></h4>
                    </div>
                </div>

                <div class="small fw-bold text-muted mb-2">Módulos e Integraciones:</div>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-1">
                    <li><i class="bi bi-check-circle-fill text-success me-1"></i> Control Escolar, Kárdex y Boletas SEP</li>
                    <li><i class="bi bi-check-circle-fill text-success me-1"></i> Portales independientes Alumno y Tutor</li>
                    <li>
                        @if($school && $school->hasFeature('billing'))
                            <i class="bi bi-check-circle-fill text-success me-1"></i> Cobranza, Colegiaturas y Recibos
                        @else
                            <i class="bi bi-dash-circle text-muted me-1"></i> Cobranza y Colegiaturas (Requiere Plan Profesional)
                        @endif
                    </li>
                    <li>
                        @if($school && $school->hasFeature('canvas'))
                            <i class="bi bi-check-circle-fill text-success me-1"></i> Integración Canvas LMS (Activo)
                        @else
                            <i class="bi bi-dash-circle text-muted me-1"></i> Canvas LMS (Disponible en Plan Integral)
                        @endif
                    </li>
                </ul>
            </div>
            <div class="card-footer bg-light py-2 text-center">
                <a href="https://wa.me/525644117635?text=Hola,%20solicito%20informaci%C3%B3n%20para%20ajustar%20el%20plan%20de%20mi%20colegio%20en%20TuCardex" target="_blank" class="btn btn-sm btn-link text-decoration-none">
                    <i class="bi bi-arrow-up-circle me-1"></i> Solicitar cambio de plan o convenio
                </a>
            </div>
        </div>
        @endif
    <div class="card">
        <div class="card-header"><span class="title"><i class="bi bi-shield-lock"></i> Roles del sistema</span></div>
        <div class="card-body p-0"><table class="table mb-0"><thead><tr><th class="ps-3">Rol</th><th>Descripción</th><th>Usuarios</th></tr></thead><tbody>
        @foreach($roles as $r)<tr><td class="ps-3"><strong>{{ $r->name }}</strong></td><td class="text-muted small">{{ $r->description }}</td><td>{{ $r->users_count }}</td></tr>@endforeach
        </tbody></table></div>
        <div class="card-body">
            <p class="text-muted small mb-0"><i class="bi bi-info-circle"></i> El ciclo escolar y el período activo definidos aquí se usan como valores por defecto al crear grupos, inscripciones y registrar calificaciones.</p>
        </div>
    </div>
    </div>
</div>
@endsection
