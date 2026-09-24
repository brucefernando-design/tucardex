@extends('layouts.app')
@section('title', 'Facturación SAT (CFDI 4.0)')

@section('content')
<div class="page-head">
    <div>
        <h1>Facturación SAT · CFDI 4.0</h1>
        <div class="breadcrumb-mini">Configuración de Comprobantes Fiscales Digitales y Complemento IEDU · México</div>
    </div>
    <a href="{{ route('facturacion.index') }}" class="btn btn-outline-secondary btn-icon"><i class="bi bi-receipt"></i> Ver facturas y recibos</a>
</div>

{{-- Banner SAT México --}}
<div class="fe-banner mb-4">
    <div class="fe-banner-main">
        <div class="fe-banner-icon"><i class="bi bi-file-earmark-check"></i></div>
        <div>
            <div class="fe-banner-title">
                Facturación Electrónica <span class="fe-flag">🇲🇽 SAT CFDI 4.0</span>
            </div>
            <div class="fe-banner-sub">Emisión automatizada de facturas de colegiatura con <strong>Complemento IEDU</strong> para deducción de impuestos de los padres de familia.</div>
        </div>
    </div>
    <div class="fe-banner-right">
        <div class="fe-sat-tag">SAT CFDI 4.0</div>
        <div class="fe-sat-caption">Instituciones Educativas Privadas</div>
    </div>
</div>

{{-- Chips de estado --}}
<div class="fe-chips mb-4">
    <span class="fe-chip {{ $settings->enabled ? 'is-on' : 'is-off' }}">
        <i class="bi bi-{{ $settings->enabled ? 'check-circle-fill' : 'slash-circle' }}"></i> {{ $settings->enabled ? 'Timbrado Activo' : 'Timbrado Inactivo' }}
    </span>
    <span class="fe-chip is-neutral"><i class="bi bi-cpu"></i> Motor: {{ ucfirst($settings->pac_driver ?? 'Simulado') }}</span>
    <span class="fe-chip {{ $settings->environment === 'produccion' ? 'is-prod' : 'is-neutral' }}">
        <i class="bi bi-shield-lock"></i> Entorno: {{ $settings->environment === 'produccion' ? 'Producción SAT' : 'Sandbox (Pruebas)' }}
    </span>
    <span class="fe-chip {{ filled($settings->rfc) ? 'is-on' : 'is-off' }}">
        <i class="bi bi-person-vcard"></i> RFC: {{ $settings->rfc ?? 'Sin RFC' }}
    </span>
    <form action="{{ route('facturacion.probar') }}" method="POST" class="ms-auto">@csrf
        <button class="btn btn-sm btn-brand btn-icon"><i class="bi bi-lightning-charge"></i> Probar conexión PAC</button>
    </form>
</div>

<form action="{{ route('facturacion.guardar') }}" method="POST">@csrf

    {{-- Estado y Modo --}}
    <div class="card card-accent mb-4">
        <div class="card-header">
            <span class="title"><i class="bi bi-lightning-charge"></i> Modo de Emisión y PAC</span>
            <span class="text-muted small">Activación y proveedor de timbrado fiscal en México</span>
        </div>
        <div class="card-body">
            <div class="fe-switch">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="enabled" name="enabled" value="1" @checked($settings->enabled)>
                </div>
                <label for="enabled" class="fe-switch-label">
                    <strong>Habilitar timbrado fiscal automático</strong>
                    <span>Al registrar o cobrar una colegiatura, se emitirá el CFDI 4.0 oficial ante el SAT.</span>
                </label>
            </div>
            <div class="fe-switch mt-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="auto_emit" name="auto_emit" value="1" @checked($settings->auto_emit)>
                </div>
                <label for="auto_emit" class="fe-switch-label">
                    <strong>Emitir y enviar por correo al momento del pago</strong>
                    <span>Envía automáticamente el XML y PDF de la factura al correo del padre de familia o tutor.</span>
                </label>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label class="form-label">Proveedor de Certificación (PAC / Driver)</label>
                    <select name="pac_driver" class="form-select">
                        <option value="simulado" @selected(($settings->pac_driver ?? 'simulado') === 'simulado')>Modo Simulado / Pruebas Internas (Genera XML sin costo)</option>
                        <option value="facturama" @selected(($settings->pac_driver ?? 'facturama') === 'facturama')>Facturama México (CFDI 4.0 + IEDU · Timbres TuCardex)</option>
                        <option value="facturapi" @selected(($settings->pac_driver ?? '') === 'facturapi')>Facturapi (API REST CFDI 4.0 + IEDU)</option>
                        <option value="finkok" @selected(($settings->pac_driver ?? '') === 'finkok')>Finkok (Timbrado PAC Directo)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Entorno de Timbrado</label>
                    <select name="environment" class="form-select">
                        <option value="beta" @selected($settings->environment === 'beta')>Sandbox / Pruebas (RFC genérico de prueba SAT)</option>
                        <option value="produccion" @selected($settings->environment === 'produccion')>Producción (Facturas reales válidas ante el SAT)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Datos del Colegio Emisor --}}
    <div class="card mb-4">
        <div class="card-header">
            <span class="title"><i class="bi bi-building"></i> Datos Fiscales del Colegio (Emisor)</span>
            <span class="text-muted small">Deben coincidir exactamente con la Constancia de Situación Fiscal del SAT</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">RFC del Colegio <span class="text-danger">*</span></label>
                    <input name="rfc" value="{{ old('rfc', $settings->rfc) }}" class="form-control text-uppercase" maxlength="13" placeholder="ESC200101XYZ">
                    <div class="form-text">12 caracteres para Personas Morales, 13 para Físicas.</div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                    <input name="razon_social" value="{{ old('razon_social', $settings->razon_social) }}" class="form-control text-uppercase" placeholder="COLEGIO EJEMPLO DE MEXICO S.C.">
                    <div class="form-text">En CFDI 4.0 debe ir sin régimen societario (sin 'S.C.' o 'S.A. de C.V.').</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Régimen Fiscal <span class="text-danger">*</span></label>
                    <select name="regimen_fiscal" class="form-select">
                        <option value="603" @selected(($settings->regimen_fiscal ?? '603') === '603')>603 - Personas Morales con Fines no Lucrativos (Colegios e Inst. de Enseñanza)</option>
                        <option value="601" @selected(($settings->regimen_fiscal ?? '') === '601')>601 - General de Ley Personas Morales</option>
                        <option value="612" @selected(($settings->regimen_fiscal ?? '') === '612')>612 - Personas Físicas con Actividades Empresariales y Profesionales</option>
                        <option value="626" @selected(($settings->regimen_fiscal ?? '') === '626')>626 - Régimen Simplificado de Confianza (RESICO)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Código Postal Fiscal <span class="text-danger">*</span></label>
                    <input name="codigo_postal" value="{{ old('codigo_postal', $settings->codigo_postal) }}" class="form-control" maxlength="5" placeholder="06700">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nombre Comercial</label>
                    <input name="nombre_comercial" value="{{ old('nombre_comercial', $settings->nombre_comercial) }}" class="form-control" placeholder="Colegio Ejemplo">
                </div>
                <div class="col-12">
                    <label class="form-label">Domicilio Fiscal (Calle, Número, Colonia, Municipio, Estado)</label>
                    <input name="direccion_fiscal" value="{{ old('direccion_fiscal', $settings->direccion_fiscal) }}" class="form-control" placeholder="Av. Insurgentes Sur 1234, Col. Del Valle, Benito Juárez, CDMX">
                </div>
            </div>
        </div>
    </div>

    {{-- Credenciales de Timbrado y Llaves --}}
    <div class="card mb-4">
        <div class="card-header">
            <span class="title"><i class="bi bi-key"></i> Llaves de API del Proveedor (PAC)</span>
            <span class="text-muted small">Token de acceso para timbrar facturas</span>
        </div>
        <div class="card-body">
            @if(config('services.facturama.user'))
            <div class="alert alert-success py-2 mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>
                    <strong>Timbres oficiales TuCardex Activos:</strong> Tu colegio tiene habilitado el timbrado fiscal CFDI 4.0 en automático. No requieres contratar ningún PAC por separado.
                </div>
            </div>
            @endif

            <div class="alert alert-info py-2">
                <i class="bi bi-info-circle me-2"></i> Con <strong>Facturama México</strong> puedes timbrar con la cuenta matriz de TuCardex o ingresar tus propias credenciales a continuación si cuentas con paquete propio.
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Usuario Facturama (Correo / Cuenta)</label>
                    <input type="text" name="client_id" value="{{ old('client_id', $settings->client_id) }}" class="form-control" placeholder="{{ config('services.facturama.user') ? 'Usando cuenta maestra TuCardex (' . config('services.facturama.user') . ')' : 'tu-correo@facturama.mx' }}">
                    <div class="form-text">Déjalo vacío para usar la bolsa de timbres de TuCardex.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contraseña Facturama</label>
                    <input type="password" name="client_secret" value="" class="form-control" placeholder="{{ $settings->client_secret ? '•••••••••••••••• (Configurada)' : (config('services.facturama.password') ? '•••••••• (Cuenta maestra TuCardex)' : '••••••••••••') }}">
                    <div class="form-text">Se almacena con cifrado AES-256 en la base de datos.</div>
                </div>
                <div class="col-md-12 mt-2">
                    <label class="form-label small text-muted">API Key alternativa (Facturapi / Finkok)</label>
                    <input type="password" name="pac_api_key" value="{{ old('pac_api_key', $settings->pac_api_key) }}" class="form-control form-control-sm" placeholder="Opcional solo si no usas Facturama">
                </div>
            </div>
        </div>
    </div>

    {{-- Catálogos SAT para Educación Privada --}}
    <div class="card mb-4">
        <div class="card-header">
            <span class="title"><i class="bi bi-bookmarks"></i> Catálogos SAT y Reglas de Colegiatura (IEDU)</span>
            <span class="text-muted small">Parámetros oficiales del Anexo 20 para servicios educativos</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Clave Prod/Serv (SAT)</label>
                    <input name="clave_prod_serv" value="{{ old('clave_prod_serv', $settings->clave_prod_serv ?: '86121500') }}" class="form-control" placeholder="86121500">
                    <div class="form-text">86121500 = Servicios de enseñanza preescolar, primaria y secundaria.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Clave de Unidad (SAT)</label>
                    <input name="clave_unidad" value="{{ old('clave_unidad', $settings->clave_unidad ?: 'E48') }}" class="form-control" placeholder="E48">
                    <div class="form-text">E48 = Unidad de servicio.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Objeto de Impuesto (SAT)</label>
                    <select name="objeto_imp" class="form-select">
                        <option value="01" @selected(($settings->objeto_imp ?: '02') === '01')>01 - No objeto de impuesto (No recomendado)</option>
                        <option value="02" @selected(($settings->objeto_imp ?: '02') === '02')>02 - Sí objeto de impuesto (IVA Exento — obligatorio con RVOE)</option>
                    </select>
                    <div class="form-text">⚠️ Usar <strong>02 + Exento</strong> para colegiaturas con RVOE (Art. 15 Fracc. IV LIVA). El valor 01 puede causar rechazo del PAC SAT.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Serie Facturas</label>
                    <input name="serie_factura" value="{{ old('serie_factura', $settings->serie_factura ?: 'F') }}" class="form-control" maxlength="10">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Serie Recibos</label>
                    <input name="serie_boleta" value="{{ old('serie_boleta', $settings->serie_boleta ?: 'REC') }}" class="form-control" maxlength="10">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Serie Notas de Crédito</label>
                    <input name="serie_nc_factura" value="{{ old('serie_nc_factura', $settings->serie_nc_factura ?: 'NC') }}" class="form-control" maxlength="10">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Moneda</label>
                    <input name="moneda" value="MXN" class="form-control" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="fe-footer">
        <a href="{{ route('facturacion.index') }}" class="btn btn-light btn-icon"><i class="bi bi-arrow-left"></i> Volver</a>
        <button class="btn btn-brand btn-icon"><i class="bi bi-check-lg"></i> Guardar configuración fiscal SAT</button>
    </div>
</form>
@endsection

@push('scripts')
<style>
.fe-banner{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;
    background:linear-gradient(135deg,#065f46,#047857);color:#fff;border-radius:var(--radius);
    padding:22px 26px;box-shadow:var(--shadow)}
.fe-banner-main{display:flex;align-items:center;gap:18px}
.fe-banner-icon{width:60px;height:60px;border-radius:16px;background:rgba(255,255,255,.16);
    display:flex;align-items:center;justify-content:center;font-size:28px;flex-shrink:0}
.fe-banner-title{font-size:22px;font-weight:800;letter-spacing:-.3px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.fe-flag{font-size:13px;font-weight:600;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:20px}
.fe-banner-sub{opacity:.92;font-size:13px;margin-top:4px;max-width:640px}
.fe-banner-right{text-align:right}
.fe-sat-tag{background:#fff;color:#065f46;font-weight:800;font-size:15px;padding:6px 16px;border-radius:10px;letter-spacing:1px;display:inline-block}
.fe-sat-caption{font-size:11px;opacity:.9;margin-top:6px}
.fe-chips{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.fe-chip{display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:600;padding:7px 14px;border-radius:30px;border:1px solid var(--line);background:#fff}
.fe-chip.is-on{background:var(--brand-soft);color:var(--brand-ink);border-color:transparent}
.fe-chip.is-off{background:#fee2e2;color:#991b1b;border-color:transparent}
.fe-chip.is-neutral{background:#eef1f4;color:#475467}
.fe-chip.is-prod{background:#fef3c7;color:#92400e}
.fe-switch{display:flex;align-items:flex-start;gap:14px;padding:14px 16px;border:1px solid var(--line);border-radius:12px;background:#f9fbfa}
.fe-switch .form-check-input{width:2.6em;height:1.4em;margin-top:2px;cursor:pointer}
.fe-switch .form-check-input:checked{background-color:var(--brand);border-color:var(--brand)}
.fe-switch-label{cursor:pointer;line-height:1.35}
.fe-switch-label span{display:block;color:var(--ink-2);font-size:12.5px;margin-top:2px}
.card-header .title{display:inline-flex;align-items:center;gap:8px}
.card-header{display:flex;align-items:baseline;gap:12px;flex-wrap:wrap}
.fe-footer{position:sticky;bottom:0;background:linear-gradient(180deg,rgba(238,243,241,0),var(--body-bg) 40%);
    display:flex;justify-content:space-between;gap:12px;padding:16px 0 8px}
</style>
@endpush
