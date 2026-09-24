<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha de Preinscripción {{ $admission->folio }}</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.5; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #16a34a; padding-bottom: 12px; margin-bottom: 20px; }
        .school-name { font-size: 18px; font-weight: bold; color: #15803d; margin: 0; }
        .folio-badge { background: #dcfce7; color: #15803d; padding: 6px 12px; font-size: 14px; font-weight: bold; border-radius: 6px; float: right; font-family: monospace; }
        .clear { clear: both; }
        .section-title { background: #f1f5f9; padding: 6px 10px; font-weight: bold; font-size: 12px; border-left: 4px solid #16a34a; margin-top: 16px; margin-bottom: 10px; text-transform: uppercase; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data td { padding: 5px 8px; font-size: 11px; }
        table.data td.label { width: 30%; color: #64748b; font-weight: bold; }
        table.data td.val { width: 70%; font-weight: bold; }
        .footer-signatures { margin-top: 50px; width: 100%; }
        .footer-signatures td { width: 50%; text-align: center; font-size: 11px; padding-top: 50px; border-top: 1px solid #94a3b8; }
        .instructions { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 10px; font-size: 10px; margin-top: 25px; border-radius: 6px; color: #475569; }
    </style>
</head>
<body>

<div class="header">
    <div class="folio-badge">FOLIO: {{ $admission->folio }}</div>
    <div class="school-name">{{ $setting->school_name ?? $school->name }}</div>
    <div style="font-size: 11px; color: #64748b;">FICHA OFICIAL DE REGISTRO Y PREINSCRIPCIÓN DE ASPIRANTE</div>
    <div style="font-size: 10px; color: #94a3b8;">Ciclo Escolar: {{ $setting->academic_year ?? date('Y').'-'.(date('Y')+1) }} · Fecha de Solicitud: {{ $admission->created_at->format('d/m/Y H:i') }}</div>
    <div class="clear"></div>
</div>

<div class="section-title">1. Datos del Aspirante</div>
<table class="data">
    <tr>
        <td class="label">Nombre Completo:</td>
        <td class="val">{{ $admission->full_name }}</td>
    </tr>
    <tr>
        <td class="label">CURP:</td>
        <td class="val">{{ $admission->curp ?? 'NO PROPORCIONADA' }}</td>
    </tr>
    <tr>
        <td class="label">Fecha de Nacimiento:</td>
        <td class="val">{{ $admission->birth_date ? $admission->birth_date->format('d/m/Y') : 'NO REGISTRADA' }} ({{ $admission->gender == 'M' ? 'Masculino' : ($admission->gender == 'F' ? 'Femenino' : '—') }})</td>
    </tr>
    <tr>
        <td class="label">Grado Solicitado:</td>
        <td class="val">{{ optional($admission->course)->name ?? 'Por asignar' }}</td>
    </tr>
    @if($admission->previous_school)
    <tr>
        <td class="label">Escuela Anterior:</td>
        <td class="val">{{ $admission->previous_school }}</td>
    </tr>
    @endif
</table>

<div class="section-title">2. Datos del Padre / Tutor Responsable</div>
<table class="data">
    <tr>
        <td class="label">Tutor Legal:</td>
        <td class="val">{{ $admission->guardian_name }} ({{ $admission->guardian_relationship }})</td>
    </tr>
    <tr>
        <td class="label">Teléfono WhatsApp:</td>
        <td class="val">{{ $admission->guardian_phone }}</td>
    </tr>
    <tr>
        <td class="label">Correo Electrónico:</td>
        <td class="val">{{ $admission->guardian_email }}</td>
    </tr>
    <tr>
        <td class="label">Domicilio:</td>
        <td class="val">{{ $admission->address ?? 'NO REGISTRADO' }}</td>
    </tr>
</table>

<div class="section-title">3. Estado de Documentación Digital</div>
<table class="data">
    <tr>
        <td class="label">Acta de Nacimiento:</td>
        <td class="val">{{ $admission->birth_certificate_path ? 'ADJUNTADA DIGITALMENTE' : 'PENDIENTE EN FÍSICO' }}</td>
    </tr>
    <tr>
        <td class="label">CURP:</td>
        <td class="val">{{ $admission->curp_path ? 'ADJUNTADA DIGITALMENTE' : 'PENDIENTE EN FÍSICO' }}</td>
    </tr>
    <tr>
        <td class="label">Comprobante Domicilio:</td>
        <td class="val">{{ $admission->address_proof_path ? 'ADJUNTADA DIGITALMENTE' : 'PENDIENTE EN FÍSICO' }}</td>
    </tr>
    <tr>
        <td class="label">Boleta / Certificado:</td>
        <td class="val">{{ $admission->previous_grades_path ? 'ADJUNTADA DIGITALMENTE' : 'PENDIENTE EN FÍSICO' }}</td>
    </tr>
</table>

<div class="instructions">
    <strong>INSTRUCCIONES PARA LA FAMILIA:</strong> Conservar este documento como comprobante de inicio de trámite. El departamento de Control Escolar evaluará el cupo y le notificará la fecha para firma de matrícula y entrega de documentos originales.
</div>

<table class="footer-signatures">
    <tr>
        <td>Firma del Padre o Tutor</td>
        <td>Sello y Firma de Control Escolar</td>
    </tr>
</table>

</body>
</html>
