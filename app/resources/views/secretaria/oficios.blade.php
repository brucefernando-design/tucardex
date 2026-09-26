@extends('layouts.app')

@section('title', 'Generador de Citatorios y Oficios Institucionales')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('secretaria.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle p-1" title="Volver a Secretaría">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h3 class="fw-bold mb-0 text-dark">Citatorios, Justificantes y Oficios</h3>
            </div>
            <p class="text-muted small mb-0">Redacta documentos oficiales, descárgalos en PDF o envíalos directamente por correo electrónico a un padre de familia o a todo un grupo escolar con el PDF adjunto.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 rounded-4 shadow-sm d-flex align-items-center gap-3 p-3 mb-4">
            <i class="bi bi-check-circle-fill fs-3 text-success"></i>
            <div>
                <div class="fw-bold">¡Operación exitosa!</div>
                <div class="small">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning border-0 rounded-4 shadow-sm d-flex align-items-center gap-3 p-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
            <div>
                <div class="fw-bold">Aviso del Sistema</div>
                <div class="small">{{ session('warning') }}</div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 rounded-4 shadow-sm d-flex align-items-center gap-3 p-3 mb-4">
            <i class="bi bi-x-circle-fill fs-3 text-danger"></i>
            <div>
                <div class="fw-bold">Atención</div>
                <div class="small">{{ session('error') }}</div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-9 mx-auto">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-paper text-primary"></i> Redacción y Envío de Oficio
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">Membrete Oficial TuKardex</span>
                </div>

                <div class="card-body p-4">
                    <form id="formOficio" method="POST" action="{{ route('secretaria.oficio.descargar') }}" target="_blank">
                        @csrf
                        
                        <!-- SECCIÓN 1: DESTINATARIO Y MODO DE ENVÍO -->
                        <div class="p-3 bg-light rounded-4 mb-4 border">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-people-fill text-primary me-2"></i>1. ¿A quién va dirigido el documento?</h6>
                            
                            <div class="d-flex flex-wrap gap-3 mb-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="destinatario_tipo" id="tipoIndividual" value="individual" checked onchange="cambiarModoDestinatario()">
                                    <label class="form-check-label fw-bold" for="tipoIndividual">
                                        <i class="bi bi-person-fill text-primary"></i> Alumno / Padre de Familia Específico
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="destinatario_tipo" id="tipoGrupo" value="grupo" onchange="cambiarModoDestinatario()">
                                    <label class="form-check-label fw-bold" for="tipoGrupo">
                                        <i class="bi bi-collection-fill text-success"></i> Todo un Grado / Grupo Escolar
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="destinatario_tipo" id="tipoManual" value="manual" onchange="cambiarModoDestinatario()">
                                    <label class="form-check-label fw-bold" for="tipoManual">
                                        <i class="bi bi-pencil-fill text-secondary"></i> Ingreso Manual / Personalizado
                                    </label>
                                </div>
                            </div>

                            <!-- MODO INDIVIDUAL: SELECTOR DE ALUMNO -->
                            <div id="panelIndividual">
                                <label class="form-label small fw-bold">Seleccionar Estudiante:</label>
                                <select name="student_id" id="selectStudent" class="form-select form-select-lg" onchange="actualizarDatosAlumno()">
                                    <option value="">-- Elige un estudiante registrado en el sistema --</option>
                                    @foreach($students as $st)
                                        @php
                                            $emailContacto = $st->email ?? optional($st->user)->email ?? '';
                                            $tutor = $st->guardian_name ?? 'Padre de Familia';
                                        @endphp
                                        <option value="{{ $st->id }}" 
                                                data-nombre="{{ $st->full_name }}" 
                                                data-tutor="{{ $tutor }}" 
                                                data-email="{{ $emailContacto }}"
                                                data-curso="{{ optional($st->course)->name }} {{ optional($st->course)->section }}">
                                            {{ $st->full_name }} — {{ optional($st->course)->name }} "{{ optional($st->course)->section }}" (Tutor: {{ $tutor }})
                                        </option>
                                    @endforeach
                                </select>

                                <!-- Información y estado del correo del alumno -->
                                <div class="mt-2" id="infoAlumnoPreview" style="display:none;">
                                    <div class="alert alert-light border rounded-3 py-2 px-3 mb-0 small d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <span class="text-muted">Tutor registrado:</span> <strong id="lblTutor" class="text-dark"></strong> &nbsp;|&nbsp;
                                            <span class="text-muted">Correo:</span> <strong id="lblEmail" class="text-primary"></strong>
                                        </div>
                                        <span class="badge" id="badgeEmailStatus"></span>
                                    </div>

                                    <!-- Campo para ingresar correo si el alumno no lo tenía -->
                                    <div id="missingEmailBox" class="mt-2 p-3 bg-warning-subtle border border-warning rounded-3" style="display:none;">
                                        <div class="fw-bold text-dark small mb-1">
                                            <i class="bi bi-envelope-plus text-warning"></i> Este alumno aún no tiene correo electrónico en su expediente.
                                        </div>
                                        <div class="text-muted small mb-2">Ingresa el correo del tutor o del alumno para enviarle el oficio ahora y <strong>guardarlo automáticamente en su expediente</strong>:</div>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="bi bi-at"></i></span>
                                            <input type="email" name="email_estudiante" id="inputEmailEstudiante" class="form-control" placeholder="correo.del.tutor@ejemplo.com">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MODO GRUPO: SELECTOR DE CURSO -->
                            <div id="panelGrupo" style="display:none;">
                                <label class="form-label small fw-bold">Seleccionar Grado o Sección:</label>
                                <select name="course_id" id="selectCourse" class="form-select form-select-lg">
                                    <option value="">-- Elige un grado / grupo completo --</option>
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}">
                                            {{ $c->full_name }} ({{ $c->level }}) — {{ $c->students_count }} Estudiantes registrados
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted mt-2">
                                    <i class="bi bi-info-circle text-primary"></i> Al seleccionar esta opción, el sistema enviará el citatorio u oficio por correo a <strong>todos los padres de familia y alumnos</strong> pertenecientes a este grupo que tengan correo registrado.
                                </div>
                            </div>

                            <!-- MODO MANUAL -->
                            <div id="panelManual" style="display:none;">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">Nombre del Destinatario:</label>
                                        <input type="text" name="destinatario_manual" id="inputDestinatarioManual" class="form-control" placeholder="Ej: C. Juan Pérez / Tutor legal">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold">Correo Electrónico de Destino:</label>
                                        <input type="email" name="email_manual" id="inputEmailManual" class="form-control" placeholder="correo@ejemplo.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Campo oculto para el destinatario impreso en el PDF -->
                            <input type="hidden" name="destinatario" id="destinatarioHidden" value="A QUIEN CORRESPONDA">
                        </div>

                        <!-- SECCIÓN 2: TIPO DE OFICIO Y REDACCIÓN -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-5">
                                <label class="form-label small fw-bold">Tipo de Documento:</label>
                                <select name="tipo" class="form-select" id="selectTipo" onchange="actualizarPlantilla()">
                                    <option value="citatorio">Citatorio a Padres / Tutor</option>
                                    <option value="justificante">Justificante Médico / Escolar</option>
                                    <option value="circular">Circular / Comunicado General</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-7">
                                <label class="form-label small fw-bold">Asunto / Título Oficial:</label>
                                <input type="text" name="asunto" id="inputAsunto" class="form-control fw-bold" value="CITATORIO URGENTE A PADRES DE FAMILIA" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Cuerpo del Mensaje / Redacción Oficial:</label>
                            <textarea name="cuerpo" id="textareaCuerpo" class="form-control" rows="5" required>Por medio de la presente, nos dirigimos a usted con la finalidad de solicitar su presencia en las instalaciones del plantel, para tratar asuntos de suma importancia relacionados con el desempeño académico y disciplinario de su hijo(a).</textarea>
                        </div>

                        <!-- DATOS ESPECÍFICOS DE LA CITA -->
                        <div class="row g-3 mb-4 p-3 bg-light rounded-3 border" id="bloqueCita">
                            <div class="col-12"><strong class="small text-uppercase text-dark"><i class="bi bi-calendar-event text-primary me-1"></i> Datos de la Reunión / Cita:</strong></div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-medium">Fecha de la Cita:</label>
                                <input type="date" name="fecha_cita" class="form-control" value="{{ now()->addDay()->format('Y-m-d') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-medium">Hora:</label>
                                <input type="time" name="hora_cita" class="form-control" value="09:00">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-medium">Lugar / Salón:</label>
                                <input type="text" name="lugar" class="form-control" value="Dirección del Plantel">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-medium">Nombre de la Autoridad que Firma:</label>
                                <input type="text" name="firmante" class="form-control" value="{{ $setting->director ?? 'Dirección General' }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-medium">Cargo:</label>
                                <input type="text" name="cargo" class="form-control" value="Director(a) del Plantel">
                            </div>
                        </div>

                        <!-- BOTONES DE ACCIÓN: DESCARGAR O ENVIAR POR CORREO -->
                        <div class="d-flex flex-wrap gap-3 pt-3 border-top">
                            <button type="button" onclick="ejecutarAccion('descargar')" class="btn btn-outline-primary rounded-pill px-4 py-2 d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-pdf"></i>
                                <span class="fw-bold">Descargar Oficio en PDF</span>
                            </button>
                            
                            <button type="button" onclick="ejecutarAccion('enviar')" class="btn btn-success rounded-pill px-4 py-2 d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-send-fill"></i>
                                <span class="fw-bold">Enviar por Correo Electrónico (con PDF adjunto)</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cambiarModoDestinatario() {
    const modo = document.querySelector('input[name="destinatario_tipo"]:checked').value;
    const panelInd = document.getElementById('panelIndividual');
    const panelGrp = document.getElementById('panelGrupo');
    const panelMan = document.getElementById('panelManual');

    panelInd.style.display = modo === 'individual' ? 'block' : 'none';
    panelGrp.style.display = modo === 'grupo' ? 'block' : 'none';
    panelMan.style.display = modo === 'manual' ? 'block' : 'none';

    if (modo === 'individual') {
        actualizarDatosAlumno();
    } else if (modo === 'grupo') {
        const selCourse = document.getElementById('selectCourse');
        const textCourse = selCourse.options[selCourse.selectedIndex]?.text || 'Grupo Escolar';
        document.getElementById('destinatarioHidden').value = 'Padres de Familia y Alumnos de ' + textCourse;
    } else {
        const manName = document.getElementById('inputDestinatarioManual').value || 'A QUIEN CORRESPONDA';
        document.getElementById('destinatarioHidden').value = manName;
    }
}

function actualizarDatosAlumno() {
    const sel = document.getElementById('selectStudent');
    const opt = sel.options[sel.selectedIndex];
    const preview = document.getElementById('infoAlumnoPreview');
    const lblTutor = document.getElementById('lblTutor');
    const lblEmail = document.getElementById('lblEmail');
    const statusBadge = document.getElementById('badgeEmailStatus');
    const missingBox = document.getElementById('missingEmailBox');

    if (opt && opt.value) {
        const nombre = opt.getAttribute('data-nombre');
        const tutor = opt.getAttribute('data-tutor');
        const email = opt.getAttribute('data-email');

        lblTutor.textContent = tutor + ' (Alumno: ' + nombre + ')';
        document.getElementById('destinatarioHidden').value = 'C. ' + tutor + ' (Tutor de ' + nombre + ')';

        if (email) {
            lblEmail.textContent = email;
            statusBadge.className = 'badge bg-success-subtle text-success';
            statusBadge.innerHTML = '<i class="bi bi-check2"></i> Correo listo';
            missingBox.style.display = 'none';
        } else {
            lblEmail.textContent = 'Sin correo registrado';
            statusBadge.className = 'badge bg-danger-subtle text-danger';
            statusBadge.innerHTML = '<i class="bi bi-exclamation-circle"></i> Falta correo';
            missingBox.style.display = 'block';
            document.getElementById('inputEmailEstudiante').focus();
        }
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
        missingBox.style.display = 'none';
        document.getElementById('destinatarioHidden').value = 'A QUIEN CORRESPONDA';
    }
}

function actualizarPlantilla() {
    const tipo = document.getElementById('selectTipo').value;
    const asunto = document.getElementById('inputAsunto');
    const cuerpo = document.getElementById('textareaCuerpo');
    const bloqueCita = document.getElementById('bloqueCita');

    if (tipo === 'citatorio') {
        asunto.value = 'CITATORIO A PADRES DE FAMILIA';
        cuerpo.value = 'Por medio de la presente, nos dirigimos a usted con la finalidad de solicitar su presencia en las instalaciones del plantel, para tratar asuntos de suma importancia relacionados con el desempeño académico y disciplinario de su hijo(a).';
        bloqueCita.style.display = 'flex';
    } else if (tipo === 'justificante') {
        asunto.value = 'JUSTIFICANTE OFICIAL DE INASISTENCIA';
        cuerpo.value = 'Se expide el presente justificante para hacer constar que el/la alumno(a) ha presentado la documentación correspondiente a las inasistencias registradas, por lo que se solicita a los docentes facilitarle la entrega de evaluaciones y actividades pendientes.';
        bloqueCita.style.display = 'none';
    } else {
        asunto.value = 'CIRCULAR INFORMATIVA';
        cuerpo.value = 'Se informa a toda la comunidad escolar y padres de familia las disposiciones vigentes para las próximas actividades cívicas y académicas a desarrollarse en nuestra institución.';
        bloqueCita.style.display = 'none';
    }
}

function ejecutarAccion(accion) {
    const form = document.getElementById('formOficio');
    const modo = document.querySelector('input[name="destinatario_tipo"]:checked').value;

    if (modo === 'individual') {
        const sel = document.getElementById('selectStudent');
        if (!sel.value) {
            alert('Por favor selecciona un estudiante.');
            sel.focus();
            return;
        }
        actualizarDatosAlumno();

        if (accion === 'enviar') {
            const opt = sel.options[sel.selectedIndex];
            const emailExistente = opt.getAttribute('data-email');
            const inputNuevo = document.getElementById('inputEmailEstudiante').value;

            if (!emailExistente && !inputNuevo) {
                alert('Este alumno no tiene correo registrado. Por favor escribe el correo del tutor o alumno en el recuadro para poder enviárselo.');
                document.getElementById('inputEmailEstudiante').focus();
                return;
            }
        }
    } else if (modo === 'grupo') {
        const selCourse = document.getElementById('selectCourse');
        if (!selCourse.value) {
            alert('Por favor selecciona un grado o grupo.');
            selCourse.focus();
            return;
        }
        document.getElementById('destinatarioHidden').value = 'Padres de Familia y Alumnos de ' + selCourse.options[selCourse.selectedIndex].text;
    } else {
        const manName = document.getElementById('inputDestinatarioManual').value;
        document.getElementById('destinatarioHidden').value = manName || 'A QUIEN CORRESPONDA';

        if (accion === 'enviar') {
            const manEmail = document.getElementById('inputEmailManual').value;
            if (!manEmail) {
                alert('Por favor ingresa el correo electrónico de destino.');
                document.getElementById('inputEmailManual').focus();
                return;
            }
        }
    }

    if (accion === 'descargar') {
        form.action = "{{ route('secretaria.oficio.descargar') }}";
        form.target = "_blank";
        form.submit();
    } else {
        form.action = "{{ route('secretaria.oficio.enviar') }}";
        form.target = "_self";
        if (confirm('¿Confirmas el envío de este oficio institucional con su PDF adjunto por correo electrónico?')) {
            form.submit();
        }
    }
}
</script>
@endsection
