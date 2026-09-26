<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preinscripciones y Admisiones · {{ $setting->school_name ?? $school->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .adm-header {
            background: linear-gradient(135deg, #0f1f18, #15803d 120%);
            color: #fff; padding: 48px 0 36px; border-bottom: 4px solid #22c55e;
        }
        .form-card {
            border-radius: 18px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            margin-top: -30px; background: #fff;
        }
        .section-tag {
            font-size: 12px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1px; color: #16a34a; margin-bottom: 4px;
        }
        .step-pill {
            width: 32px; height: 32px; border-radius: 50%; background: #dcfce7;
            color: #16a34a; display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px;
        }
    </style>
</head>
<body>

<header class="adm-header text-center">
    <div class="container" style="max-width: 850px;">
        <div style="width: 72px; height: 72px; border-radius: 18px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 36px;">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <h1 class="fw-bold mb-1">{{ $setting->school_name ?? $school->name }}</h1>
        <p class="text-white-50 mb-2">Proceso de Admisiones y Preinscripciones en Línea</p>
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill" style="background: rgba(255,255,255,0.15); font-size: 13px;">
            <i class="bi bi-calendar-check"></i> Ciclo Escolar {{ $setting->academic_year ?? date('Y') . '-' . (date('Y')+1) }}
        </div>
    </div>
</header>

<main class="container py-4 mb-5" style="max-width: 850px;">
    <div class="card form-card p-4 p-md-5">
        <div class="mb-4 text-center">
            <h3 class="fw-bold mb-1">Ficha de Registro del Aspirante</h3>
            <p class="text-muted small">Por favor completa la información requerida para iniciar el expediente escolar de tu hijo(a).</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger shadow-sm">
                <ul class="mb-0 small">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admissions.public_submit', $school->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- SECCIÓN 1: DATOS DEL ALUMNO -->
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                    <span class="step-pill">1</span>
                    <div>
                        <div class="section-tag">Paso 1 de 3</div>
                        <h5 class="fw-bold mb-0">Datos del Aspirante</h5>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nombre(s) del Alumno(a) *</label>
                        <input name="first_name" value="{{ old('first_name') }}" class="form-control" placeholder="Ej. Mateo Alejandro" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Apellidos del Alumno(a) *</label>
                        <input name="last_name" value="{{ old('last_name') }}" class="form-control" placeholder="Ej. Rodríguez Garza" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">CURP (México)</label>
                        <input name="curp" value="{{ old('curp') }}" class="form-control font-monospace text-uppercase" placeholder="18 caracteres" maxlength="18">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Fecha de Nacimiento</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Género</label>
                        <select name="gender" class="form-select">
                            <option value="">Selecciona...</option>
                            <option value="M" @selected(old('gender')=='M')>Masculino</option>
                            <option value="F" @selected(old('gender')=='F')>Femenino</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Grado o Curso al que Aspira *</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Seleccionar Grado Escolar...</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}" @selected(old('course_id')==$c->id)>{{ $c->name }} "{{ $c->section }}" ({{ $c->shift ?? 'Matutino' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Escuela de Procedencia</label>
                        <input name="previous_school" value="{{ old('previous_school') }}" class="form-control" placeholder="Nombre de la escuela anterior (si aplica)">
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: DATOS DEL PADRE O TUTOR -->
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                    <span class="step-pill">2</span>
                    <div>
                        <div class="section-tag">Paso 2 de 3</div>
                        <h5 class="fw-bold mb-0">Datos del Padre, Madre o Tutor</h5>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Nombre Completo del Tutor *</label>
                        <input name="guardian_name" value="{{ old('guardian_name') }}" class="form-control" placeholder="Ej. Roberto Rodríguez Santos" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Parentesco *</label>
                        <select name="guardian_relationship" class="form-select" required>
                            <option value="Madre" @selected(old('guardian_relationship')=='Madre')>Madre</option>
                            <option value="Padre" @selected(old('guardian_relationship')=='Padre')>Padre</option>
                            <option value="Tutor Legal" @selected(old('guardian_relationship')=='Tutor Legal')>Tutor Legal</option>
                            <option value="Abuelo(a)" @selected(old('guardian_relationship')=='Abuelo(a)')>Abuelo(a)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Teléfono Celular (WhatsApp) *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-whatsapp text-success"></i> +52</span>
                            <input type="tel" name="guardian_phone" value="{{ old('guardian_phone') }}" class="form-control" placeholder="10 dígitos (ej. 8671234567)" required>
                        </div>
                        <div class="form-text">Recibirás tu folio oficial y avisos de admisión en este WhatsApp.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Correo Electrónico *</label>
                        <input type="email" name="guardian_email" value="{{ old('guardian_email') }}" class="form-control" placeholder="tutor@ejemplo.com" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Domicilio Particular</label>
                        <input name="address" value="{{ old('address') }}" class="form-control" placeholder="Calle, Número, Colonia, Municipio">
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 3: DOCUMENTACIÓN DIGITAL -->
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                    <span class="step-pill">3</span>
                    <div>
                        <div class="section-tag">Paso 3 de 3</div>
                        <h5 class="fw-bold mb-0">Documentación Digital (Opcional para agilizar trámite)</h5>
                    </div>
                </div>

                <p class="small text-muted mb-3">Puedes adjuntar los documentos ahora en formato digital (PDF o foto clara) o entregarlos posteriormente en ventanilla de Control Escolar.</p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold"><i class="bi bi-file-earmark-person me-1"></i> Acta de Nacimiento</label>
                        <input type="file" name="birth_certificate" class="form-control form-control-sm" accept="image/*,.pdf">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold"><i class="bi bi-card-heading me-1"></i> CURP del Aspirante</label>
                        <input type="file" name="curp_doc" class="form-control form-control-sm" accept="image/*,.pdf">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold"><i class="bi bi-house me-1"></i> Comprobante de Domicilio</label>
                        <input type="file" name="address_proof" class="form-control form-control-sm" accept="image/*,.pdf">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold"><i class="bi bi-award me-1"></i> Boleta o Certificado Anterior</label>
                        <input type="file" name="previous_grades" class="form-control form-control-sm" accept="image/*,.pdf">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Observaciones Médicas, Alergias o Cuidados Especiales</label>
                        <textarea name="medical_notes" class="form-control" rows="2" placeholder="Indicar si el aspirante padece alguna alergia o condición médica relevante.">{{ old('medical_notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-top text-center">
                <button type="submit" class="btn btn-success py-3 px-5 fw-bold fs-5 shadow" style="border-radius: 14px;">
                    <i class="bi bi-send-check-fill me-2"></i> Enviar Solicitud de Preinscripción
                </button>
                <div class="text-muted small mt-2">
                    <i class="bi bi-shield-check text-success me-1"></i> Tus datos están protegidos bajo estricta confidencialidad escolar.
                </div>
            </div>
        </form>
    </div>
</main>

<footer class="text-center py-4 text-muted small border-top bg-white">
    <p class="mb-1"><strong>{{ $setting->school_name ?? $school->name }}</strong> · {{ $setting->address ?? 'México' }}</p>
    <p class="mb-0">Sistema Escolar y Admisiones impulsado por <strong>TuKardex</strong></p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
