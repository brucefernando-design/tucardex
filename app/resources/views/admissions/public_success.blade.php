<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitud Recibida · {{ $setting->school_name ?? $school->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; min-height: 100vh; display: flex; flex-direction: column; }
    </style>
</head>
<body>

<div class="container py-5 my-auto" style="max-width: 680px;">
    <div class="card shadow border-0 text-center" style="border-radius: 20px; overflow: hidden;">
        <div class="py-5 px-4 text-white" style="background: linear-gradient(135deg, #15803d, #166534);">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 42px;">
                <i class="bi bi-check-lg"></i>
            </div>
            <h2 class="mb-1 text-white fw-bold">¡Solicitud de Preinscripción Recibida!</h2>
            <div class="text-white-50">El registro ha sido almacenado en el sistema escolar con éxito</div>
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="p-3 rounded-3 border mb-4 text-start" style="background: #f8fafc;">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Folio Oficial de Aspirante</span>
                    <strong class="font-monospace fs-5 text-primary">{{ $admission->folio }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Aspirante</span>
                    <strong>{{ $admission->full_name }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Grado Solicitado</span>
                    <span>{{ optional($admission->course)->name ?? 'Asignación pendiente' }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Tutor</span>
                    <span>{{ $admission->guardian_name }} ({{ $admission->guardian_relationship }})</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Teléfono de Contacto</span>
                    <span>{{ $admission->guardian_phone }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Estado del Trámite</span>
                    <span class="badge bg-warning text-dark border px-2 py-1"><i class="bi bi-clock me-1"></i> En Espera de Revisión</span>
                </div>
            </div>

            <div class="alert alert-info py-3 px-3 text-start small mb-4">
                <i class="bi bi-info-circle-fill fs-5 me-2 text-primary"></i>
                <strong>Próximos pasos:</strong> El área de Control Escolar de <strong>{{ $setting->school_name ?? $school->name }}</strong> revisará la documentación del aspirante y se pondrá en contacto contigo a través de WhatsApp o llamada telefónica para darte la resolución y bienvenida formal.
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('admissions.public_pdf', ['slug' => $school->slug, 'folio' => $admission->folio]) }}" target="_blank" class="btn btn-primary py-3 fw-bold fs-6 rounded-3 shadow-sm">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Descargar Ficha de Preinscripción en PDF
                </a>
                <a href="{{ route('admissions.public_form', $school->slug) }}" class="btn btn-outline-secondary py-2 rounded-3">
                    <i class="bi bi-arrow-left me-1"></i> Registrar a otro Aspirante
                </a>
            </div>
        </div>
    </div>
</div>

<footer class="text-center py-3 text-muted small mt-auto">
    {{ $setting->school_name ?? $school->name }} · Admisiones TuCardex
</footer>

</body>
</html>
