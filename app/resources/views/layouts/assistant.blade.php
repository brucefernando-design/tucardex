{{-- Cardex Copiloto — Asistente Interactivo en Vivo con Motor Semántico Exhaustivo --}}
<div id="cardex-assistant-root">
    <!-- Botón Flotante para abrir copiloto -->
    <button id="cardex-fab" class="cardex-fab" title="Cardex Copiloto · Tu Asistente Escolar">
        <span class="cardex-fab-pulse"></span>
        <i class="bi bi-robot cardex-fab-icon"></i>
        <span class="cardex-fab-label d-none d-sm-inline">Asistente</span>
    </button>

    <!-- Barra fija superior de Modo Explorador (Mouse Hover) -->
    <div id="cardex-inspector-bar" class="cardex-inspector-bar d-none">
        <div class="d-flex align-items-center gap-2">
            <span class="spinner-grow spinner-grow-sm text-warning" role="status"></span>
            <div>
                <strong>Modo Explorador Activo:</strong> Pasa el puntero sobre cualquier botón, campo, columna o tarjeta para ver su función específica.
            </div>
        </div>
        <button id="btnExitInspector" class="btn btn-sm btn-light text-dark fw-bold shadow-sm">
            <i class="bi bi-x-circle me-1"></i> Salir del Explorador (ESC)
        </button>
    </div>

    <!-- Tooltip flotante inteligente para Modo Explorador -->
    <div id="cardex-inspector-tooltip" class="cardex-inspector-tooltip d-none">
        <div class="cardex-tt-header">
            <i class="bi bi-info-circle-fill text-success me-1"></i>
            <span id="cardex-tt-title" class="fw-bold">Elemento</span>
        </div>
        <div id="cardex-tt-desc" class="cardex-tt-body">Descripción...</div>
        <div id="cardex-tt-tip" class="cardex-tt-tip">Tip...</div>
    </div>

    <!-- Panel / Ventana Flotante del Asistente -->
    <div id="cardex-panel" class="cardex-panel d-none">
        <div class="cardex-panel-head">
            <div class="d-flex align-items-center gap-2">
                <div class="cardex-avatar"><i class="bi bi-robot"></i></div>
                <div>
                    <div class="fw-bold text-white fs-6">Cardex Copiloto</div>
                    <div class="small text-white-50" style="font-size:11px">Guía Escolar y Fiscal para México</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button id="btnToggleInspectFromPanel" class="btn-head-icon" title="Activar Explorador con Mouse">
                    <i class="bi bi-cursor-fill"></i>
                </button>
                <button id="btnMinimizePanel" class="btn-head-icon" title="Minimizar"><i class="bi bi-dash-lg"></i></button>
                <button id="btnClosePanel" class="btn-head-icon" title="Cerrar"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>

        <div class="cardex-panel-body">
            <!-- Buscador Rápido -->
            <div class="cardex-search-box">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="cardex-search-input" class="form-control form-control-sm" placeholder="¿Cómo hago...? (ej. facturar, boleta, notas, spei)">
                <button id="btnClearSearch" class="btn-clear d-none"><i class="bi bi-x"></i></button>
            </div>

            <!-- Resultados de Búsqueda (dinámico) -->
            <div id="cardex-search-results" class="cardex-search-results d-none"></div>

            <!-- Contenido Contextual Principal -->
            <div id="cardex-contextual-content">
                <!-- Tarjeta de la Página Actual -->
                <div class="cardex-card">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="cardex-badge-page" id="cardex-page-name">Página actual</span>
                    </div>
                    <p class="cardex-page-summary mb-2" id="cardex-page-summary">Cargando sugerencias de esta pantalla...</p>
                    <ul class="cardex-quick-steps" id="cardex-quick-steps"></ul>
                </div>

                <!-- Botón Modo Explorador Destacado -->
                <div class="cardex-inspect-banner" id="btnBannerInspect">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-circle"><i class="bi bi-cursor-fill"></i></div>
                        <div>
                            <div class="fw-bold" style="font-size:13px">Modo Explorador (Mouse)</div>
                            <div class="text-muted" style="font-size:11px">Pasa el cursor por cualquier elemento para conocer su propósito</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>

                <!-- Preguntas Frecuentes del Módulo -->
                <div class="mt-3">
                    <div class="text-muted text-uppercase fw-bold mb-2" style="font-size:11px;letter-spacing:0.5px">Preguntas Rápidas del Módulo</div>
                    <div class="cardex-accordion" id="cardex-faqs"></div>
                </div>
            </div>
        </div>

        <div class="cardex-panel-footer">
            <span class="text-muted small" style="font-size:11px">TuCardex SEP · SAT CFDI 4.0</span>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="switchAssistantPersist" checked>
                <label class="form-check-label text-muted small" for="switchAssistantPersist" style="font-size:11px">Activo</label>
            </div>
        </div>
    </div>
</div>

<style>
/* === Estilos del Asistente Cardex Copiloto === */
.cardex-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1050;
    background: linear-gradient(135deg, #15803d, #16a34a);
    color: #ffffff;
    border: none;
    border-radius: 50px;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 8px 24px rgba(22, 163, 74, 0.4);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.cardex-fab:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 12px 30px rgba(22, 163, 74, 0.55);
}
.cardex-fab-pulse {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4ade80;
    box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
    animation: cardexPulse 2s infinite;
}
@keyframes cardexPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(74, 222, 128, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
}

/* Panel Flotante */
.cardex-panel {
    position: fixed;
    bottom: 84px;
    right: 24px;
    width: 385px;
    max-width: calc(100vw - 32px);
    height: 550px;
    max-height: calc(100vh - 110px);
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 16px 48px rgba(15, 31, 24, 0.22), 0 0 0 1px rgba(0,0,0,0.06);
    z-index: 1060;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: cardexSlideUp 0.25s ease-out;
}
@keyframes cardexSlideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.cardex-panel-head {
    background: linear-gradient(135deg, #0f241a, #15803d);
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.cardex-avatar {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(255,255,255,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
}
.btn-head-icon {
    background: transparent;
    border: none;
    color: rgba(255,255,255,0.75);
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: 0.15s;
}
.btn-head-icon:hover {
    background: rgba(255,255,255,0.2);
    color: #ffffff;
}

.cardex-panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 14px;
    background: #f8faf9;
}
.cardex-search-box {
    position: relative;
    margin-bottom: 12px;
}
.cardex-search-box input {
    padding-left: 32px;
    padding-right: 28px;
    border-radius: 10px;
    border: 1px solid #d1ded6;
    background: #ffffff;
}
.cardex-search-box .search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #8da497;
    font-size: 13px;
}
.cardex-search-box .btn-clear {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #8da497;
    cursor: pointer;
}

.cardex-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e3ebe6;
    padding: 12px;
    margin-bottom: 12px;
}
.cardex-badge-page {
    background: #e8f5ec;
    color: #166534;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
}
.cardex-page-summary {
    font-size: 12.5px;
    color: #374151;
    line-height: 1.45;
}
.cardex-quick-steps {
    list-style: none;
    padding: 0;
    margin: 0;
}
.cardex-quick-steps li {
    font-size: 12px;
    color: #4b5563;
    padding: 4px 0;
    display: flex;
    gap: 6px;
}
.cardex-quick-steps li i {
    color: #16a34a;
    font-size: 13px;
    flex-shrink: 0;
}

.cardex-inspect-banner {
    background: #ffffff;
    border: 1px dashed #22c55e;
    border-radius: 12px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: 0.18s;
}
.cardex-inspect-banner:hover {
    background: #f0fdf4;
    border-color: #16a34a;
    transform: translateX(2px);
}
.cardex-inspect-banner .icon-circle {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: #dcfce7;
    color: #15803d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

/* FAQ Accordion */
.cardex-faq-item {
    background: #ffffff;
    border: 1px solid #e5ede8;
    border-radius: 8px;
    margin-bottom: 6px;
    overflow: hidden;
}
.cardex-faq-q {
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #1f2937;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.cardex-faq-q:hover { background: #f9fafb; }
.cardex-faq-a {
    padding: 8px 12px;
    font-size: 11.5px;
    color: #4b5563;
    line-height: 1.45;
    background: #fdfefe;
    border-top: 1px solid #f3f4f6;
    display: none;
}
.cardex-faq-item.active .cardex-faq-a { display: block; }
.cardex-faq-item.active .cardex-faq-q i { transform: rotate(180deg); }

/* Barra Superior de Inspector */
.cardex-inspector-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 48px;
    background: #0f241a;
    color: #ffffff;
    z-index: 1090;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
    font-size: 13.5px;
    animation: cardexSlideDown 0.2s ease-out;
}
@keyframes cardexSlideDown {
    from { transform: translateY(-100%); }
    to { transform: translateY(0); }
}

/* Tooltip de Inspector */
.cardex-inspector-tooltip {
    position: fixed;
    z-index: 1100;
    width: 310px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 12px 36px rgba(0,0,0,0.22), 0 0 0 1px rgba(0,0,0,0.08);
    pointer-events: none;
    padding: 12px 14px;
    font-size: 12px;
    transition: opacity 0.1s ease;
}
.cardex-tt-header {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
}
.cardex-tt-body {
    color: #374151;
    line-height: 1.45;
    margin-bottom: 6px;
    font-size: 12px;
}
.cardex-tt-tip {
    background: #f0fdf4;
    color: #166534;
    border-left: 3px solid #22c55e;
    padding: 5px 8px;
    border-radius: 4px;
    font-size: 11px;
    line-height: 1.35;
}

/* Resalte de elemento inspeccionado */
.cardex-inspected-element {
    outline: 3px solid #22c55e !important;
    outline-offset: 2px !important;
    cursor: help !important;
}

.cardex-panel-footer {
    background: #ffffff;
    border-top: 1px solid #eef2f0;
    padding: 8px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>

<script>
(function() {
    'use strict';

    // 1. Base de conocimiento contextual por ruta de TuCardex
    const knowledgeBase = {
        'facturacion': {
            name: 'Facturación Electrónica SAT (CFDI 4.0)',
            summary: 'Emisión oficial de comprobantes fiscales con Complemento IEDU para colegiaturas deducibles de impuestos en México.',
            steps: [
                'Verifica que el timbrado SAT esté activo arriba a la derecha.',
                'Descarga el PDF oficial con código QR o el XML fiscal con un clic.',
                'Para tutores sin RFC personal, el sistema timbra automáticamente con el RFC genérico XAXX010101000.'
            ],
            faqs: [
                {
                    q: '¿Qué es el Complemento IEDU?',
                    a: 'Es el formato oficial del SAT para que los tutores deduzcan colegiaturas de nivel preescolar a bachillerato en su declaración anual (ISR). Incluye CURP, nivel escolar y RVOE.'
                },
                {
                    q: '¿Por qué dice IVA Exento?',
                    a: 'Por mandato fiscal en México (Art. 15 Fracc. IV LIVA), los servicios educativos oficiales con RVOE no causan IVA (tasa 0% Exento, ObjetoImp 02).'
                },
                {
                    q: '¿Cómo cancelar una factura ante el SAT?',
                    a: 'Haz clic en el botón con la cruz roja al final de la fila del comprobante. TuCardex enviará la solicitud formal de cancelación directamente ante el SAT.'
                }
            ]
        },
        'grades': {
            name: 'Calificaciones Escolares (SEP)',
            summary: 'Captura y control de notas con la escala oficial mexicana base 10 (5.0 a 10.0 con decimales).',
            steps: [
                'Las calificaciones se registran estrictamente de 5.0 a 10.0.',
                '6.0 es la calificación mínima aprobatoria oficial en México.',
                'Usa la Planilla Masiva para asentar las notas de todo el grupo rápidamente.'
            ],
            faqs: [
                {
                    q: '¿Puedo capturar notas con decimales como 8.5?',
                    a: 'Sí, el sistema acepta números decimales (ej. 6.4, 7.8, 9.5, 10.0).'
                },
                {
                    q: '¿Cómo imprimo la Boleta oficial?',
                    a: 'Entra a Alumnos o Grupo, abre la ficha del estudiante y haz clic en "Descargar Boleta de Calificaciones".'
                }
            ]
        },
        'attendances': {
            name: 'Pase de Lista y Asistencia',
            summary: 'Control de presencia diaria con cálculo del requisito del 80% mínimo de asistencia de la SEP.',
            steps: [
                'Selecciona tu grupo y la fecha de la sesión.',
                'Marca: Presente (P), Retardo (T), Justificado (J) o Falta (F).',
                'El sistema calcula automáticamente si el alumno cumple el 80% para tener derecho a acreditar.'
            ],
            faqs: [
                {
                    q: '¿Qué pasa si un alumno tiene menos del 80%?',
                    a: 'La boleta oficial de la SEP marcará automáticamente "✗ No cumple" con el mínimo de asistencia requerido.'
                }
            ]
        },
        'payments': {
            name: 'Colegiaturas y Finanzas',
            summary: 'Registro de cobros, adeudos, transferencias SPEI y timbrado fiscal inmediato.',
            steps: [
                'Registra abonos o liquidaciones de colegiaturas con recibo foliado.',
                'Si el timbrado SAT está activo, generará el CFDI 4.0 al instante.',
                'Consulta el reporte de morosos para dar seguimiento a saldos pendientes.'
            ],
            faqs: [
                {
                    q: '¿Cómo pagan los padres por SPEI?',
                    a: 'En Configuración registra la cuenta CLABE interbancaria (18 dígitos) del colegio. El portal del padre mostrará los datos para transferencia electrónica.'
                }
            ]
        },
        'students': {
            name: 'Alumnos y Expedientes',
            summary: 'Directorio escolar con CURP oficial de 18 caracteres, boletas y credenciales escolares con QR.',
            steps: [
                'Asegúrate de capturar la CURP completa de 18 caracteres para validez SEP e IEDU.',
                'Genera la credencial escolar del estudiante en PDF con código QR.',
                'Consulta el estado de cuenta y boleta desde la ficha del alumno.'
            ],
            faqs: [
                {
                    q: '¿Para qué sirve la CURP en el sistema?',
                    a: 'Es indispensable para que el SAT autorice la deducción fiscal de la colegiatura y para la validez de la boleta de acreditación ante la SEP.'
                }
            ]
        },
        'settings': {
            name: 'Configuración Institucional',
            summary: 'Datos del colegio: CCT, RVOE, Director, Cédula Profesional y Cuentas SPEI.',
            steps: [
                'Verifica que la Clave de Centro de Trabajo (CCT) sea la oficial asignada por la SEP.',
                'Registra el número de RVOE otorgado por la SEP.',
                'Agrega la Cédula Profesional del Director para que se imprima al pie de las boletas oficiales.'
            ],
            faqs: [
                {
                    q: '¿Para qué sirve la Cédula Profesional?',
                    a: 'La SEP requiere que las boletas de acreditación oficial lleven la firma del Director del plantel junto con su número de Cédula Profesional.'
                }
            ]
        },
        'schools': {
            name: 'Gestión Global de Colegios (Super Admin)',
            summary: 'Panel del dueño de la plataforma para dar de alta colegios, pausar suscripciones y monitorear ingresos.',
            steps: [
                'Da de alta colegios con el botón "+ Dar de Alta Colegio".',
                'Pausa o suspende colegios con adeudos de suscripción para restringir el acceso.',
                'Revisa ingresos cobrados en pesos ($ MXN) y total de alumnos por institución.'
            ],
            faqs: [
                {
                    q: '¿Qué pasa al suspender un colegio?',
                    a: 'Inmediatamente ningún director, maestro o padre de esa escuela podrá iniciar sesión hasta que lo reactives.'
                }
            ]
        },
        'default': {
            name: 'Panel de Control TuCardex',
            summary: 'Tu plataforma de control escolar integral para instituciones educativas en México.',
            steps: [
                'Navega con el menú lateral para acceder a Alumnos, Calificaciones, Pagos y Facturación SAT.',
                'Activa el Modo Explorador para conocer cualquier función pasando el mouse.',
                'Usa este copiloto en cualquier momento si tienes dudas de un módulo.'
            ],
            faqs: [
                {
                    q: '¿Cómo cambio los datos del colegio?',
                    a: 'Ve a Configuración en el menú lateral para actualizar el nombre, CCT, RVOE y Cédula Profesional del Director.'
                },
                {
                    q: '¿Puedo ocultar este asistente?',
                    a: 'Sí, haz clic en el interruptor de abajo o en Cerrar. Puedes volverlo a abrir cuando quieras desde la barra superior.'
                }
            ]
        }
    };

    // 2. DICCIONARIO SEMÁNTICO EXHAUSTIVO PARA CAMPOS DE FORMULARIO (INPUTS, SELECTS, TEXTAREAS)
    const formFieldsDictionary = {
        'curp': {
            title: 'Campo: CURP del Alumno',
            desc: 'Clave Única de Registro de Población oficial mexicana de 18 caracteres alfanuméricos.',
            tip: 'Indispensable para el timbrado del Complemento IEDU ante el SAT y para las boletas oficiales de la SEP.'
        },
        'rfc': {
            title: 'Campo: RFC del Emisor / Receptor',
            desc: 'Registro Federal de Contribuyentes ante el SAT (12 caracteres morales, 13 físicas).',
            tip: 'Para padres sin RFC personal, el sistema usa el genérico nacional XAXX010101000.'
        },
        'cedula_profesional': {
            title: 'Campo: Cédula Profesional del Director',
            desc: 'Número de cédula profesional con validez oficial expedida por la Dirección General de Profesiones de la SEP.',
            tip: 'Se imprime de forma automática al pie de la Boleta de Calificaciones oficial debajo de la firma.'
        },
        'cct': {
            title: 'Campo: Clave de Centro de Trabajo (CCT)',
            desc: 'Identificador único asignado por la SEP al plantel escolar (ej. 09PPR1452Z).',
            tip: 'Aparece en el membrete superior de todas las boletas oficiales, credenciales y constancias.'
        },
        'rvoe': {
            title: 'Campo: Número de Acuerdo RVOE',
            desc: 'Reconocimiento de Validez Oficial de Estudios otorgado por la SEP.',
            tip: 'El SAT exige que este número vaya estampado dentro del XML del Complemento IEDU para deducción fiscal.'
        },
        'objeto_imp': {
            title: 'Campo: Objeto de Impuesto SAT',
            desc: 'Define si la colegiatura causa impuestos ante el SAT. En México para educación es "02" (Sí objeto).',
            tip: 'Al ser Objeto 02 con IVA Exento (Art. 15 Fracc. IV LIVA), los padres pueden deducirlo en su declaración anual.'
        },
        'clave_prod_serv': {
            title: 'Campo: Clave Producto/Servicio SAT',
            desc: 'Código oficial del catálogo del SAT para servicios de educación. El código oficial es 86121500 (Servicios de enseñanza).',
            tip: 'Requerido para que el SAT certifique el CFDI 4.0 con Complemento IEDU sin errores.'
        },
        'clave_unidad': {
            title: 'Campo: Clave de Unidad SAT',
            desc: 'Unidad de medida conforme al catálogo del SAT. Para colegiaturas se utiliza E48 (Unidad de servicio).',
            tip: 'Representa el servicio lectivo mensual o periodo educativo.'
        },
        'regimen_fiscal': {
            title: 'Campo: Régimen Fiscal SAT',
            desc: 'Régimen tributario de la escuela. Generalmente 603 (Personas Morales con Fines no Lucrativos).',
            tip: 'Debe coincidir con la Constancia de Situación Fiscal actualizada del colegio ante el SAT.'
        },
        'codigo_postal': {
            title: 'Campo: Código Postal Fiscal (SAT)',
            desc: 'Código postal de 5 dígitos del domicilio fiscal del emisor o receptor.',
            tip: 'El SAT CFDI 4.0 valida estrictamente que coincida con el registrado en el padrón tributario.'
        },
        'direccion_fiscal': {
            title: 'Campo: Domicilio Fiscal',
            desc: 'Calle, número exterior/interior, colonia y municipio del colegio o tutor.',
            tip: 'Se incluye en los recibos y comprobantes oficiales.'
        },
        'razon_social': {
            title: 'Campo: Razón Social Fiscal',
            desc: 'Nombre legal de la persona moral o física registrada ante el SAT (sin régimen de capital en CFDI 4.0).',
            tip: 'Debe capturarse exactamente en mayúsculas como figura en la Constancia de Situación Fiscal.'
        },
        'nombre_comercial': {
            title: 'Campo: Nombre Comercial del Colegio',
            desc: 'Nombre público o institucional de la escuela que verán los padres y alumnos en la plataforma.',
            tip: 'Se utiliza en credenciales escolares, portal web y boletas.'
        },
        'spei_clabe': {
            title: 'Campo: Cuenta CLABE Interbancaria (18 dígitos)',
            desc: 'Cuenta estandarizada para recibir transferencias electrónicas SPEI de colegiaturas.',
            tip: 'Se almacena con cifrado bancario AES-256 en la base de datos y se muestra en el portal de padres.'
        },
        'spei_bank': {
            title: 'Campo: Banco Receptor SPEI',
            desc: 'Institución bancaria donde la escuela tiene su cuenta (ej. BBVA, Santander, Banorte, Citibanamex).',
            tip: 'Orienta a los tutores al dar de alta la cuenta en su banca móvil.'
        },
        'spei_beneficiary': {
            title: 'Campo: Beneficiario de Cuenta SPEI',
            desc: 'Nombre exacto del titular de la cuenta bancaria escolar para validar la transferencia.',
            tip: 'Evita rechazos en el sistema SPEI del Banco de México.'
        },
        'spei_instructions': {
            title: 'Campo: Instrucciones de Pago SPEI',
            desc: 'Guía personalizada para los tutores (ej. "Colocar como concepto la matrícula del alumno").',
            tip: 'Aparece visible para los padres al seleccionar pago por transferencia bancaria.'
        },
        'spei_enabled': {
            title: 'Interruptor: Habilitar Pago por SPEI',
            desc: 'Activa o desactiva la opción de transferencias electrónicas para los padres de familia.',
            tip: 'Al estar activo, el portal de tutores mostrará la cuenta CLABE interbancaria.'
        },
        'score': {
            title: 'Campo: Calificación SEP (5.0 a 10.0)',
            desc: 'Evaluación numérica del estudiante en la escala oficial de la SEP con un decimal.',
            tip: '6.0 es el mínimo aprobatorio. 5.0 es la nota mínima oficial en caso de no acreditar.'
        },
        'scores': {
            title: 'Campo: Calificaciones Masivas del Grupo',
            desc: 'Permite asentar simultáneamente las notas de todos los alumnos en escala SEP 5.0 a 10.0.',
            tip: 'Guarda al final de la página para registrar todas las calificaciones del grupo.'
        },
        'first_name': {
            title: 'Campo: Nombre(s) del Estudiante / Usuario',
            desc: 'Nombre de pila oficial conforme al acta de nacimiento o identificación legal.',
            tip: 'Se utiliza para actas de examen, boletas y certificados oficiales.'
        },
        'last_name': {
            title: 'Campo: Apellidos del Estudiante / Usuario',
            desc: 'Apellido paterno y materno completos.',
            tip: 'Indispensable para el padrón oficial escolar y reportes de la SEP.'
        },
        'birth_date': {
            title: 'Campo: Fecha de Nacimiento',
            desc: 'Fecha de nacimiento del alumno para determinar su edad lectiva y validar su CURP.',
            tip: 'Requisito para los informes estadísticos oficiales del formato 911 de la SEP.'
        },
        'gender': {
            title: 'Campo: Género del Alumno',
            desc: 'Identificación de género (Masculino / Femenino) para estadísticas oficiales de la SEP.',
            tip: 'Se reporta en los censos educativos anuales.'
        },
        'blood_type': {
            title: 'Campo: Tipo de Sangre y RH',
            desc: 'Grupo sanguíneo del alumno para el expediente médico y credencial escolar de emergencia.',
            tip: 'Información vital para primeros auxilios en el plantel escolar.'
        },
        'address': {
            title: 'Campo: Domicilio Particular',
            desc: 'Dirección de residencia del alumno o tutor para expedientes y contacto institucional.',
            tip: 'Permite ubicar la zona de influencia de la escuela.'
        },
        'dni': {
            title: 'Campo: Matrícula / Clave de Alumno',
            desc: 'Número de control escolar interno que identifica al alumno en el colegio.',
            tip: 'Aparece en la credencial escolar, código de barras y estado de cuenta.'
        },
        'guardian_name': {
            title: 'Campo: Nombre del Padre o Tutor Legal',
            desc: 'Titular responsable de las obligaciones escolares, autorizaciones y facturación.',
            tip: 'Recibe el comprobante fiscal deducible con Complemento IEDU.'
        },
        'guardian_phone': {
            title: 'Campo: Teléfono del Padre o Tutor',
            desc: 'Número de contacto directo para llamadas o avisos de emergencia escolar.',
            tip: 'Se utiliza para avisos importantes y recordatorios de colegiatura.'
        },
        'phone': {
            title: 'Campo: Teléfono Institucional / Contacto',
            desc: 'Número telefónico oficial para comunicación y contacto directo.',
            tip: 'Se imprime en credenciales y recibos de pago.'
        },
        'email': {
            title: 'Campo: Correo Electrónico',
            desc: 'Dirección electrónica para envío de boletas, avisos y comprobantes fiscales CFDI 4.0.',
            tip: 'Sirve como usuario de inicio de sesión en el portal TuCardex.'
        },
        'password': {
            title: 'Campo: Contraseña de Acceso',
            desc: 'Clave confidencial de inicio de sesión protegida con encriptación bcrypt.',
            tip: 'Se recomienda usar al menos 8 caracteres combinando letras y números.'
        },
        'password_confirmation': {
            title: 'Campo: Confirmar Contraseña',
            desc: 'Repite la contraseña para verificar que no haya errores de escritura al crear la cuenta.',
            tip: 'Ambas contraseñas deben ser idénticas.'
        },
        'academic_year': {
            title: 'Campo: Ciclo Escolar Oficial',
            desc: 'Año lectivo activo conforme al calendario oficial publicado por la SEP (ej. 2026 - 2027).',
            tip: 'Determina la vigencia de los planes de estudio y boletas emitidas.'
        },
        'active_period': {
            title: 'Campo: Periodo de Evaluación Activo',
            desc: 'Trimestre o parcial que se está cursando o calificando actualmente.',
            tip: 'Define en qué columna se registrarán las notas capturadas por los docentes.'
        },
        'level': {
            title: 'Campo: Nivel Educativo',
            desc: 'Etapa escolar autorizada por la SEP (Preescolar, Primaria, Secundaria, Preparatoria).',
            tip: 'Determina los topes de deducción fiscal del Complemento IEDU ante el SAT.'
        },
        'grade': {
            title: 'Campo: Grado Escolar',
            desc: 'Año lectivo que cursa el estudiante dentro de su nivel (ej. 1°, 2°, 3°).',
            tip: 'Asocia las asignaturas correspondientes a la malla curricular.'
        },
        'section': {
            title: 'Campo: Grupo Escolar',
            desc: 'Sección asignada al alumno (ej. Grupo A, Grupo B).',
            tip: 'Organiza las listas de asistencia y pase de lista de los profesores.'
        },
        'classroom': {
            title: 'Campo: Salón de Clases / Aula',
            desc: 'Espacio físico asignado para las clases del grupo dentro de la escuela.',
            tip: 'Facilita la organización de horarios y capacidad de cupo.'
        },
        'shift': {
            title: 'Campo: Turno Escolar',
            desc: 'Horario del servicio educativo (Matutino, Vespertino o Completo).',
            tip: 'Se incluye en las estadísticas oficiales del formato 911 de la SEP.'
        },
        'tuition_amount': {
            title: 'Campo: Monto de Colegiatura Mensual ($ MXN)',
            desc: 'Importe regular en pesos mexicanos que se cobra a los estudiantes del colegio.',
            tip: 'Se sugiere automáticamente al generar las cuotas del ciclo lectivo.'
        },
        'amount': {
            title: 'Campo: Monto a Cobrar ($ MXN)',
            desc: 'Importe de la operación. Por ley (Art. 15 Fracc. IV LIVA), las colegiaturas son Exentas de IVA.',
            tip: 'Este importe es el valor deducible en la declaración de ISR del tutor.'
        },
        'concept': {
            title: 'Campo: Concepto de Pago',
            desc: 'Descripción del cobro escolar (ej. "Colegiatura Septiembre 2026", "Inscripción Anual").',
            tip: 'Aparece impreso en el recibo de caja y en la descripción del CFDI 4.0.'
        },
        'due_date': {
            title: 'Campo: Fecha Límite de Pago',
            desc: 'Día límite para liquidar la colegiatura antes de que el sistema la marque con adeudo.',
            tip: 'Ayuda al control oportuno de cobranza y finanzas escolares.'
        },
        'date': {
            title: 'Campo: Fecha del Registro / Operación',
            desc: 'Día en que se realiza la transacción o evento en la plataforma.',
            tip: 'Establece la cronología en los reportes financieros y bitácora escolar.'
        },
        'fecha': {
            title: 'Campo: Fecha de Emisión o Sesión',
            desc: 'Fecha correspondiente al pase de lista o emisión del comprobante escolar.',
            tip: 'Determina el periodo de asistencia o mes fiscal aplicable.'
        },
        'method': {
            title: 'Campo: Forma / Método de Pago',
            desc: 'Medio utilizado para cubrir el pago (Efectivo, Transferencia SPEI, Tarjeta Débito/Crédito).',
            tip: 'En facturación SAT, transferencias usan clave "03" y efectivo "01".'
        },
        'status': {
            title: 'Campo: Estado del Registro',
            desc: 'Condición del expediente o cobro (Activo, Pendiente, Pagado, Suspendido).',
            tip: 'Permite clasificar los registros para seguimiento y filtros.'
        },
        'remarks': {
            title: 'Campo: Observaciones Pedagógicas',
            desc: 'Anotaciones del docente sobre conducta, desempeño o justificaciones del alumno.',
            tip: 'Se pueden consultar en el historial académico del estudiante.'
        },
        'comment': {
            title: 'Campo: Comentarios o Justificaciones',
            desc: 'Detalle adicional sobre la asistencia o trámite registrado.',
            tip: 'Útil para documentar justificantes médicos de faltas.'
        },
        'hire_date': {
            title: 'Campo: Fecha de Contratación del Docente',
            desc: 'Día en que el profesor comenzó labores en la institución.',
            tip: 'Sirve para expedientes de personal y cómputo de antigüedad laboral.'
        },
        'hours_per_week': {
            title: 'Campo: Horas Semanales de Clase',
            desc: 'Carga horaria asignada al docente para impartir la asignatura.',
            tip: 'Permite calcular horarios y compensación académica.'
        },
        'specialty': {
            title: 'Campo: Especialidad o Licenciatura',
            desc: 'Área de formación profesional del docente (ej. Matemáticas, Pedagogía, Idiomas).',
            tip: 'Acredita el perfil idóneo ante supervisiones escolares de la SEP.'
        },
        'isbn': {
            title: 'Campo: Código ISBN del Libro',
            desc: 'Número Estándar Internacional que identifica la obra en la biblioteca escolar.',
            tip: 'Facilita la búsqueda y catalogación bibliográfica.'
        },
        'author': {
            title: 'Campo: Autor de la Obra',
            desc: 'Escritor o entidad creadora del libro o material educativo.',
            tip: 'Permite a los alumnos buscar títulos por autor.'
        },
        'editorial': {
            title: 'Campo: Editorial del Libro',
            desc: 'Sello editorial que publica el ejemplar disponible en la biblioteca.',
            tip: 'Útil para control de ediciones y textos escolares.'
        },
        'capacity': {
            title: 'Campo: Capacidad Máxima de Alumnos',
            desc: 'Límite de estudiantes que pueden estar asignados a este salón o grupo escolar.',
            tip: 'Previene la sobrepoblación en las aulas.'
        },
        'quantity': {
            title: 'Campo: Cantidad de Ejemplares',
            desc: 'Número de copias físicas disponibles para préstamo en la biblioteca escolar.',
            tip: 'El sistema descuenta automáticamente cada ejemplar prestado.'
        },
        'search': {
            title: 'Filtro de Búsqueda Rápida',
            desc: 'Permite encontrar registros al instante escribiendo un nombre, matrícula, CURP o folio.',
            tip: 'Presiona Enter o haz clic en Filtrar para actualizar la lista.'
        },
        'pac_api_key': {
            title: 'Campo: Llave Secreta API de Facturapi (PAC SAT)',
            desc: 'Token de autenticación proporcionado por el PAC para timbrado oficial de CFDI 4.0 ante el SAT.',
            tip: 'Se almacena cifrada con AES-256 en la base de datos de TuCardex.'
        },
        'pac_driver': {
            title: 'Campo: Proveedor de Timbrado SAT',
            desc: 'Conector fiscal configurado para México (Facturapi PAC con CFDI 4.0 + IEDU).',
            tip: 'Garantiza conexión directa y sellado ante los servidores del SAT.'
        },
        'auto_emit': {
            title: 'Interruptor: Timbrado Automático al Cobrar',
            desc: 'Al registrar un pago de colegiatura, genera y timbra automáticamente el CFDI 4.0 ante el SAT.',
            tip: 'Ahorra tiempo administrativo al emitir comprobantes de inmediato.'
        },
        'mercadopago_public_key': {
            title: 'Campo: Llave Pública de Mercado Pago México',
            desc: 'Credencial pública para habilitar cobros con tarjeta en el portal de tutores.',
            tip: 'Permite procesar cobros de forma segura sin almacenar números de tarjeta.'
        },
        'mercadopago_access_token': {
            title: 'Campo: Token de Acceso de Mercado Pago',
            desc: 'Credencial privada para validar transacciones y conciliar colegiaturas cobradas.',
            tip: 'Se guarda con cifrado AES-256 en el servidor.'
        },
        'stripe_public_key': {
            title: 'Campo: Llave Publicable de Stripe México',
            desc: 'Credencial para recibir pagos internacionales y con tarjeta de crédito/débito.',
            tip: 'Utilizada para suscripciones y cobros seguros en línea.'
        },
        'stripe_secret_key': {
            title: 'Campo: Llave Secreta de Stripe',
            desc: 'Token de servidor para autorización de cargos y reembolsos bancarios.',
            tip: 'Cifrada con AES-256 en la base de datos de TuCardex.'
        },
        'admin_name': {
            title: 'Campo: Nombre del Administrador del Colegio',
            desc: 'Titular responsable de la administración de la escuela en la plataforma SaaS.',
            tip: 'Recibirá las credenciales maestras de acceso para su plantel.'
        },
        'admin_email': {
            title: 'Campo: Correo Institucional de Administración',
            desc: 'Cuenta de correo electrónico oficial para gestión administrativa y soporte.',
            tip: 'Servirá como usuario para iniciar sesión en el panel del colegio.'
        },
        'admin_password': {
            title: 'Campo: Contraseña Inicial de Administrador',
            desc: 'Clave confidencial para la cuenta del nuevo colegio creado en el sistema.',
            tip: 'El administrador podrá actualizarla una vez que inicie sesión.'
        }
    };

    // 3. MOTOR SEMÁNTICO ULTRA-GRANULAR PARA MODO EXPLORADOR (MOUSE HOVER)
    function resolveElementSemantics(target) {
        // A) Botones y Enlaces de Acción Específica
        const btnText = (target.innerText || '').trim().toLowerCase();
        const href = (target.getAttribute('href') || '').toLowerCase();
        const titleAttr = (target.getAttribute('title') || '').toLowerCase();

        if (href.includes('pdf') || btnText.includes('pdf') || titleAttr.includes('pdf') || target.querySelector('.bi-file-earmark-pdf, .bi-file-pdf')) {
            return {
                title: 'Botón: Descargar PDF Oficial con QR y Sellos',
                desc: 'Genera o descarga el documento oficial en PDF con código bidimensional QR, cadena digital y logotipos del colegio.',
                tip: 'Listo para imprimir, enviar por correo o archivar en el expediente escolar.'
            };
        }
        if (href.includes('xml') || btnText.includes('xml') || titleAttr.includes('xml') || target.querySelector('.bi-filetype-xml, .bi-file-earmark-code')) {
            return {
                title: 'Botón: Descargar XML Timbrado SAT (CFDI 4.0 + IEDU)',
                desc: 'Descarga el comprobante fiscal electrónico nativo con el sello digital del SAT y el Complemento IEDU conforme al Anexo 20.',
                tip: 'Este es el archivo con plena validez legal que los contadores requieren para deducir colegiaturas.'
            };
        }
        if (btnText.includes('verificar') || titleAttr.includes('verificar') || href.includes('sat.gob.mx')) {
            return {
                title: 'Acción: Verificar Validez Fiscal ante el SAT',
                desc: 'Abre la consulta en línea en el portal del SAT para corroborar que el Folio Fiscal (UUID) esté vigente en sus registros.',
                tip: 'Garantiza transparencia y certeza tributaria a los padres de familia.'
            };
        }
        if (btnText.includes('cancelar') || titleAttr.includes('cancelar') || target.querySelector('.bi-x-circle, .bi-slash-circle')) {
            if (target.closest('table') || href.includes('cancel') || href.includes('anular')) {
                return {
                    title: 'Botón: Solicitar Cancelación Fiscal ante el SAT',
                    desc: 'Envía una solicitud formal de revocación del comprobante fiscal ante el SAT.',
                    tip: 'Al cancelarse, el documento quedará sin efectos fiscales en TuCardex y en el SAT.'
                };
            }
        }
        if (btnText.includes('reintentar') || titleAttr.includes('reintentar') || btnText.includes('reenviar')) {
            return {
                title: 'Botón: Reintentar Timbrado SAT',
                desc: 'Vuelve a enviar la información del pago al PAC Facturapi para obtener la certificación del SAT.',
                tip: 'Úsalo si hubo una interrupción temporal de internet o si corregiste el RFC del cliente.'
            };
        }
        if (href.includes('boletin') || btnText.includes('boleta') || titleAttr.includes('boleta')) {
            return {
                title: 'Botón: Generar Boleta de Calificaciones SEP',
                desc: 'Genera el reporte oficial de calificaciones con notas en escala 5.0 a 10.0, porcentaje de asistencia, CURP y firma con Cédula Profesional del Director.',
                tip: 'Cumple al 100% con los lineamientos de acreditación de la Secretaría de Educación Pública.'
            };
        }
        if (btnText.includes('dar de alta colegio') || target.getAttribute('data-bs-target') === '#modalNuevoColegio') {
            return {
                title: 'Botón: Dar de Alta Nuevo Colegio',
                desc: 'Abre el asistente del Superadmin para crear una nueva escuela con su propia base de datos aislada y periodo de prueba.',
                tip: 'Permite incorporar instituciones al SaaS de manera inmediata.'
            };
        }
        if (btnText.includes('guardar inscripción')) {
            return {
                title: 'Botón: Confirmar Inscripción del Alumno',
                desc: 'Registra formalmente al estudiante en el ciclo escolar, genera su expediente y apertura su cuenta financiera.',
                tip: 'Genera de inmediato el carnet y credencial del alumno.'
            };
        }
        if (btnText.includes('guardar nota')) {
            return {
                title: 'Botón: Guardar Calificación Escolar SEP',
                desc: 'Asienta la evaluación del alumno en la escala oficial de 5.0 a 10.0 en la base de datos.',
                tip: 'Recuerda que 6.0 es el mínimo aprobatorio oficial.'
            };
        }
        if (btnText.includes('guardar pago')) {
            return {
                title: 'Botón: Registrar Cobro de Colegiatura',
                desc: 'Aplica el pago en el estado de cuenta del alumno y, si el timbrado está activo, genera el CFDI 4.0 con complemento IEDU.',
                tip: 'Emite el recibo oficial foliado al instante.'
            };
        }
        if (btnText.includes('registrar préstamo')) {
            return {
                title: 'Botón: Registrar Préstamo de Biblioteca',
                desc: 'Asigna el libro al alumno indicando la fecha límite de devolución para control bibliotecario.',
                tip: 'El sistema descuenta automáticamente el ejemplar de la existencia.'
            };
        }
        if (btnText.includes('filtrar') || btnText.includes('buscar') || target.querySelector('.bi-filter, .bi-search')) {
            if (target.tagName === 'BUTTON' || target.tagName === 'A') {
                return {
                    title: 'Botón: Aplicar Filtros de Búsqueda',
                    desc: 'Actualiza la tabla mostrando únicamente los registros que coinciden con los criterios seleccionados.',
                    tip: 'Limpia los campos y presiona este botón para volver a ver la lista completa.'
                };
            }
        }
        if (btnText.includes('guardar') || (target.tagName === 'BUTTON' && target.type === 'submit')) {
            return {
                title: 'Botón: Guardar Modificaciones',
                desc: 'Aplica y guarda de forma segura los cambios realizados en este formulario en la base de datos escolar.',
                tip: 'Si hay campos obligatorios faltantes, el sistema los resaltará para que los completes.'
            };
        }

        // B) Encabezados de Tabla (TH)
        if (target.tagName === 'TH') {
            const thText = target.innerText.trim().toLowerCase();
            const thMap = {
                'folio fiscal': { title: 'Encabezado: Folio Fiscal (UUID SAT)', desc: 'Identificador fiscal único universal de 32 dígitos emitido por el SAT tras timbrar el CFDI 4.0.', tip: 'Garantiza la autenticidad del comprobante ante las autoridades tributarias.' },
                'uuid': { title: 'Encabezado: UUID Fiscal SAT', desc: 'Folio digital asignado por el PAC/SAT a cada factura electrónica timbrada.', tip: 'Puedes copiarlo para cotejarlo en el validador oficial del SAT.' },
                'curp': { title: 'Encabezado: CURP del Alumno', desc: 'Clave Única de Registro de Población (18 caracteres) del estudiante evaluado o facturado.', tip: 'Requisito de validez ante la SEP y para el Complemento IEDU.' },
                'rfc': { title: 'Encabezado: RFC del Tutor / Receptor', desc: 'Registro Federal de Contribuyentes para expedición y deducción de comprobantes fiscales.', tip: 'Si es XAXX010101000 ampara venta a Público en General.' },
                'monto': { title: 'Encabezado: Monto en Pesos ($ MXN)', desc: 'Importe de la cuota o colegiatura. Los servicios educativos oficiales con RVOE son Exentos de IVA (Art. 15 LIVA).', tip: 'Corresponde al valor total deducible en la declaración de ISR del padre.' },
                'total': { title: 'Encabezado: Importe Total ($ MXN)', desc: 'Suma monetaria total liquidada o facturada con IVA Exento.', tip: 'Importe oficial que figura en el CFDI 4.0.' },
                'alumno': { title: 'Encabezado: Alumno Matriculado', desc: 'Nombre completo del estudiante inscrito en el ciclo escolar activo.', tip: 'Haz clic en el nombre del alumno para abrir su expediente escolar.' },
                'estudiante': { title: 'Encabezado: Nombre del Estudiante', desc: 'Estudiante titular del expediente escolar, calificaciones y adeudos.', tip: 'Permite consultar el historial académico completo.' },
                'tutor': { title: 'Encabezado: Padre o Tutor Legal', desc: 'Titular responsable de las cuotas escolares y receptor de facturas electrónicas.', tip: 'Recibe notificaciones y boletas al correo registrado.' },
                'nota': { title: 'Encabezado: Calificación Oficial SEP', desc: 'Puntaje numérico en escala oficial base 10 (5.0 a 10.0 con decimales).', tip: '6.0 es el mínimo aprobatorio. 5.0 es la nota mínima oficial.' },
                'calificación': { title: 'Encabezado: Calificación Oficial SEP', desc: 'Evaluación académica en escala mexicana base 10 (5.0 a 10.0).', tip: 'Se consolida automáticamente en la boleta oficial.' },
                'promedio': { title: 'Encabezado: Promedio Escolar', desc: 'Media ponderada de calificaciones en escala oficial SEP 5.0 a 10.0.', tip: 'Se imprime en la boleta oficial de fin de ciclo lectivo.' },
                'prom.': { title: 'Encabezado: Promedio de Aprovechamiento', desc: 'Cálculo de aprovechamiento en escala oficial SEP base 10.', tip: 'Un promedio de 6.0 en adelante indica ciclo acreditado.' },
                'estado': { title: 'Encabezado: Estado del Registro / Timbrado', desc: 'Indica la condición actual: Aceptado ante SAT, Pendiente, Moroso o Activo.', tip: 'Verde = En regla · Amarillo = En espera · Rojo = Requiere atención.' },
                'situación': { title: 'Encabezado: Situación Escolar', desc: 'Condición administrativa del estudiante en el plantel (Regular, Baja, Condicionado).', tip: 'Actualizable desde el expediente del alumno.' },
                'fecha': { title: 'Encabezado: Fecha y Hora de Registro', desc: 'Momento de emisión o cobro. Determina el periodo lectivo y el mes de deducción fiscal.', tip: 'El SAT valida la fecha y hora de certificación del CFDI 4.0.' },
                'ciclo': { title: 'Encabezado: Ciclo Escolar', desc: 'Periodo lectivo oficial según el calendario de la Secretaría de Educación Pública.', tip: 'Determina el marco temporal de las boletas y actas de evaluación.' },
                'grado': { title: 'Encabezado: Grado y Nivel Educativo', desc: 'Año y etapa que cursa el estudiante (Preescolar, Primaria, Secundaria, Preparatoria).', tip: 'Asocia el plan de estudios autorizado correspondiente.' },
                'grupo': { title: 'Encabezado: Grupo / Salón de Clases', desc: 'Sección asignada a los estudiantes (ej. Grupo A, Grupo B).', tip: 'Organiza las listas de asistencia y pase de lista.' },
                'materia': { title: 'Encabezado: Asignatura Oficial', desc: 'Materia que forma parte de la malla curricular autorizada por la SEP.', tip: 'Cada materia cuenta con sus propios criterios de evaluación y parciales.' },
                'matrícula': { title: 'Encabezado: Matrícula Escolar', desc: 'Número de control interno asignado al estudiante en la institución.', tip: 'Se utiliza para búsquedas rápidas, credenciales y pagos en ventanilla.' },
                'acción': { title: 'Encabezado: Acciones y Comprobantes', desc: 'Opciones directas para descargar PDF, XML, consultar detalles o editar el registro.', tip: 'Herramientas de gestión rápida por cada fila de datos.' },
                'acciones': { title: 'Encabezado: Herramientas Operativas', desc: 'Opciones disponibles: descargar boletas, timbres, editar o consultar el registro.', tip: 'Haz clic en el icono correspondiente para ejecutar la acción.' },
                'boletas': { title: 'Encabezado: Boleta Oficial SEP', desc: 'Acceso directo a la generación y descarga de la boleta de calificaciones en PDF.', tip: 'Incluye calificaciones base 10, % de asistencia y Cédula Profesional del Director.' },
                'p': { title: 'Encabezado Asistencia: P (Presente)', desc: 'Columna para marcar asistencia en la sesión escolar.', tip: 'Suma positivamente al 80% mínimo de asistencia requerido por la SEP.' },
                't': { title: 'Encabezado Asistencia: T (Tardanza / Retardo)', desc: 'Registro de impuntualidad escolar en la sesión lectiva.', tip: 'Permite dar seguimiento a hábitos de puntualidad del alumno.' },
                'j': { title: 'Encabezado Asistencia: J (Falta Justificada)', desc: 'Inasistencia respaldada con comprobante médico o justificación institucional.', tip: 'No penaliza la acreditación académica del estudiante.' },
                'f': { title: 'Encabezado Asistencia: F (Falta Injustificada)', desc: 'Ausencia sin justificante formal en la sesión de clases.', tip: 'Resta directamente al 80% mínimo de asistencia requerido por la SEP.' },
                '%': { title: 'Encabezado: Porcentaje de Asistencia Acumulada', desc: 'Porcentaje de sesiones asistidas en el ciclo escolar.', tip: 'La SEP exige un mínimo de 80% para tener derecho a acreditación.' }
            };

            for (const k in thMap) {
                if (thText.includes(k)) return thMap[k];
            }

            return {
                title: `Encabezado: Columna "${target.innerText.trim()}"`,
                desc: 'Clasificación de datos en esta lista del sistema escolar TuCardex.',
                tip: 'Usa los filtros superiores para ordenar o encontrar registros específicos.'
            };
        }

        // C) Celdas de Tabla (TD) o Elementos de Datos dentro de Celdas
        const td = target.closest('td');
        if (td) {
            const table = td.closest('table');
            if (table) {
                const colIndex = td.cellIndex;
                const th = table.querySelector(`thead tr th:nth-child(${colIndex + 1})`);
                const colHeader = th ? th.innerText.trim().toLowerCase() : '';
                const cellText = td.innerText.trim();

                if (colHeader.includes('folio fiscal') || colHeader.includes('uuid')) {
                    return {
                        title: `Folio Fiscal SAT (UUID): ${cellText.slice(0, 18)}...`,
                        desc: 'Código fiscal universal único emitido por el SAT tras timbrar este CFDI 4.0.',
                        tip: 'Usa los botones de la derecha para descargar su PDF con código QR o su XML timbrado.'
                    };
                }
                if (colHeader.includes('curp')) {
                    return {
                        title: `CURP Registrada: ${cellText}`,
                        desc: 'Clave Única de Población oficial del alumno validada para validez SEP e IEDU.',
                        tip: 'Requisito estricto para que la colegiatura sea deducible de impuestos.'
                    };
                }
                if (colHeader.includes('rfc')) {
                    const isGeneric = cellText.toUpperCase().includes('XAXX010101000');
                    return {
                        title: `RFC: ${cellText} ${isGeneric ? '(Público en General)' : '(Contribuyente)'}`,
                        desc: isGeneric ? 'Factura emitida con el RFC genérico del SAT para tutores que no requirieron deducción personal.' : 'RFC personal del tutor registrado para deducción de colegiaturas en el IEDU.',
                        tip: isGeneric ? 'Aún con RFC genérico, el CFDI con IEDU es válido para control escolar.' : 'El padre de familia podrá deducir este gasto en su declaración anual.'
                    };
                }
                if (colHeader.includes('monto') || colHeader.includes('total') || colHeader.includes('precio')) {
                    return {
                        title: `Importe: ${cellText} MXN`,
                        desc: 'Monto de la colegiatura o cuota. En México, los servicios educativos oficiales son Exentos de IVA (Art. 15 Fracc. IV LIVA).',
                        tip: 'Representa el valor fiscal deducible para el tutor ante el SAT.'
                    };
                }
                if (colHeader.includes('alumno') || colHeader.includes('estudiante')) {
                    return {
                        title: `Estudiante: ${cellText}`,
                        desc: 'Alumno matriculado en este ciclo escolar.',
                        tip: 'Haz clic en su nombre para abrir su expediente escolar, historial de notas y pagos.'
                    };
                }
                if (colHeader.includes('tutor') || colHeader.includes('padre')) {
                    return {
                        title: `Tutor: ${cellText}`,
                        desc: 'Padre o tutor legal registrado para responsabilidades escolares y facturación fiscal.',
                        tip: 'Recibe los avisos institucionales y facturas al correo registrado.'
                    };
                }
                if (colHeader.includes('nota') || colHeader.includes('calificación') || colHeader.includes('promedio') || colHeader.includes('prom.')) {
                    const num = parseFloat(cellText.replace(',', '.'));
                    const isPassing = !isNaN(num) && num >= 6.0;
                    return {
                        title: `Calificación SEP: ${cellText} ${!isNaN(num) ? (isPassing ? '(Aprobatoria ✓)' : '(No Aprobatoria ✗)') : ''}`,
                        desc: `Evaluación académica en escala oficial SEP 5.0 a 10.0. ${isPassing ? 'Cumple con el mínimo aprobatorio oficial (≥ 6.0).' : 'No alcanza el mínimo aprobatorio de 6.0.'}`,
                        tip: 'Se promedia automáticamente en la boleta oficial del ciclo lectivo.'
                    };
                }
                if (colHeader === 'p' || colHeader === 't' || colHeader === 'j' || colHeader === 'f') {
                    const letters = { 'p': 'Presente (Asistencia)', 't': 'Tardanza (Retardo)', 'j': 'Justificado (Con comprobante)', 'f': 'Falta Injustificada' };
                    return {
                        title: `Asistencia: ${letters[colHeader] || cellText}`,
                        desc: 'Registro diario de pase de lista para el cumplimiento del 80% mínimo de asistencia de la SEP.',
                        tip: 'El porcentaje acumulado se imprime en la boleta de calificaciones.'
                    };
                }
                if (colHeader.includes('estado') || colHeader.includes('situación')) {
                    return {
                        title: `Estado: ${cellText}`,
                        desc: 'Condición actual de este registro en la plataforma TuCardex.',
                        tip: 'Verde = Todo en regla · Amarillo = Pendiente · Rojo = Requiere atención.'
                    };
                }
                if (colHeader.includes('fecha')) {
                    return {
                        title: `Fecha: ${cellText}`,
                        desc: 'Momento exacto en que se registró o timbró la operación escolar.',
                        tip: 'Determina el periodo académico y el mes de deducción fiscal del comprobante.'
                    };
                }
                if (colHeader.includes('materia')) {
                    return {
                        title: `Asignatura: ${cellText}`,
                        desc: 'Materia escolar correspondiente a la tira curricular oficial de la SEP.',
                        tip: 'Las evaluaciones de esta materia se integran a la boleta oficial.'
                    };
                }
                if (colHeader.includes('matrícula') || colHeader.includes('código')) {
                    return {
                        title: `Matrícula: ${cellText}`,
                        desc: 'Identificador único del alumno dentro del control escolar del colegio.',
                        tip: 'Se utiliza para credenciales escolares y cobros en caja.'
                    };
                }
                if (colHeader.includes('grado') || colHeader.includes('grupo') || colHeader.includes('curso')) {
                    return {
                        title: `Grado / Grupo: ${cellText}`,
                        desc: 'Nivel y aula escolar a la que pertenece el alumno o profesor.',
                        tip: 'Organiza las listas de asistencia y planillas de notas.'
                    };
                }

                if (colHeader) {
                    return {
                        title: `Dato (${th.innerText.trim()}): ${cellText.slice(0, 30)}`,
                        desc: `Información registrada en la columna "${th.innerText.trim()}".`,
                        tip: 'Puedes gestionar este dato con las opciones de la fila.'
                    };
                }
            }
        }

        // D) Formulario: Inputs, Selects y Textareas por Atributo Name
        const nameAttr = (target.getAttribute('name') || '').toLowerCase();
        if (nameAttr) {
            for (const key in formFieldsDictionary) {
                if (nameAttr.includes(key)) {
                    return formFieldsDictionary[key];
                }
            }
        }

        // E) Formulario: Resolución Inteligente por <label> Asociada
        if (target.matches('input, select, textarea')) {
            let labelElem = null;
            if (target.id) {
                labelElem = document.querySelector(`label[for="${target.id}"]`);
            }
            if (!labelElem) {
                labelElem = target.closest('.mb-3, .mb-2, .col-md-6, .col-md-4, .col-md-8, .col-12, form-group')?.querySelector('label');
            }
            if (labelElem) {
                const labelText = labelElem.innerText.replace('*', '').trim();
                const lLower = labelText.toLowerCase();

                for (const key in formFieldsDictionary) {
                    if (lLower.includes(key.replace('_', ' ')) || lLower.includes(key)) {
                        return formFieldsDictionary[key];
                    }
                }

                return {
                    title: `Campo: ${labelText}`,
                    desc: `Introduce o selecciona los datos requeridos para "${labelText}" en este registro institucional.`,
                    tip: 'Asegúrate de que la información sea correcta para la validez de los documentos oficiales.'
                };
            }
        }

        // F) Tarjetas de Estadísticas (KPIs)
        const statCard = target.closest('.stat-card');
        if (statCard) {
            const label = statCard.querySelector('.label')?.innerText.trim() || '';
            const value = statCard.querySelector('.value')?.innerText.trim() || '';
            const lLower = label.toLowerCase();

            if (lLower.includes('aceptad') || lLower.includes('timbrad')) {
                return {
                    title: `Métrica: Facturas Aceptadas SAT (${value})`,
                    desc: 'Total de comprobantes fiscales timbrados exitosamente con el PAC Facturapi ante el SAT.',
                    tip: 'Cuentan con sello digital, código QR y archivos PDF/XML listos.'
                };
            }
            if (lLower.includes('pendiente')) {
                return {
                    title: `Métrica: Facturas Pendientes (${value})`,
                    desc: 'Comprobantes en cola o pendientes de pago antes de timbrarse.',
                    tip: 'Se timbrarán de inmediato una vez registrado el cobro.'
                };
            }
            if (lLower.includes('rechazad') || lLower.includes('error')) {
                return {
                    title: `Métrica: Facturas con Error (${value})`,
                    desc: 'Comprobantes no certificados (ej. RFC inválido o falla temporal de comunicación).',
                    tip: 'Revisa el motivo del rechazo en la fila para corregir y reintentar.'
                };
            }
            if (lLower.includes('total') || lLower.includes('facturado') || lLower.includes('ingreso')) {
                return {
                    title: `Métrica: Monto Total Facturado (${value})`,
                    desc: 'Suma acumulada de colegiaturas e ingresos timbrados ante el SAT en pesos mexicanos ($ MXN).',
                    tip: 'Ingresos oficiales registrados en el ciclo escolar lectivo.'
                };
            }
            if (lLower.includes('alumno') || lLower.includes('estudiante')) {
                return {
                    title: `Métrica: Alumnos Matriculados (${value})`,
                    desc: 'Total de estudiantes registrados en el colegio en el ciclo escolar activo.',
                    tip: 'Consulta su distribución en el módulo de Alumnos.'
                };
            }
            if (lLower.includes('promedio')) {
                return {
                    title: `Métrica: Promedio General SEP (${value})`,
                    desc: 'Aprovechamiento académico global del colegio en escala oficial 5.0 a 10.0.',
                    tip: 'Un promedio institucional mayor a 6.0 indica aprovechamiento aprobatorio.'
                };
            }
            if (lLower.includes('asistencia')) {
                return {
                    title: `Métrica: Asistencia General (${value})`,
                    desc: 'Porcentaje global de asistencia estudiantil acumulada en el ciclo.',
                    tip: 'La SEP establece un 80% mínimo para derecho a acreditación.'
                };
            }

            return {
                title: `Métrica: ${label} (${value})`,
                desc: 'Indicador en tiempo real calculado automáticamente con los registros de tu colegio.',
                tip: 'Se actualiza al instante con cada cobro, calificación o pase de lista.'
            };
        }

        // G) Estados y Badges (.badge, .badge-soft)
        const badge = target.closest('.badge, .badge-soft');
        if (badge) {
            const bText = badge.innerText.trim().toLowerCase();
            if (bText.includes('aceptado') || bText.includes('timbrado')) {
                return {
                    title: 'Estado: Aceptado ante el SAT ✓',
                    desc: 'El CFDI 4.0 cuenta con sello digital oficial del SAT y folio fiscal UUID válido.',
                    tip: 'Los archivos PDF y XML están listos para descargarse.'
                };
            }
            if (bText.includes('pendiente')) {
                return {
                    title: 'Estado: Pendiente de Timbrado / Pago ⏳',
                    desc: 'El registro está guardado pero espera recepción de pago o envío al SAT.',
                    tip: 'Registra el cobro o haz clic en enviar para timbrar.'
                };
            }
            if (bText.includes('aprobado')) {
                return {
                    title: 'Estado: Aprobado (≥ 6.0 SEP) ✓',
                    desc: 'El alumno obtuvo una calificación igual o superior a 6.0 oficial de la SEP.',
                    tip: 'Acredita satisfactoriamente la asignatura o periodo escolar.'
                };
            }
            if (bText.includes('reprobado')) {
                return {
                    title: 'Estado: Reprobado (< 6.0 SEP) ✗',
                    desc: 'La calificación es inferior al mínimo aprobatorio oficial de 6.0 (calificación mínima 5.0).',
                    tip: 'El estudiante requiere periodo de recuperación o extraordinario.'
                };
            }
            if (bText.includes('activo')) {
                return {
                    title: 'Estado: Activo / En Regla',
                    desc: 'El registro o suscripción se encuentra vigente y operando con normalidad.',
                    tip: 'No requiere ninguna acción correctiva.'
                };
            }
            if (bText.includes('suspendido') || bText.includes('pausado')) {
                return {
                    title: 'Estado: Suspendido / Pausado',
                    desc: 'El acceso a este colegio o cuenta se encuentra restringido temporalmente.',
                    tip: 'Cambia el estado a Activo desde la ficha para restaurar el acceso.'
                };
            }
            if (bText.includes('vencido') || bText.includes('moroso')) {
                return {
                    title: 'Estado: Vencido / Con Adeudo',
                    desc: 'La fecha límite de pago ha pasado sin haberse liquidado la cuota.',
                    tip: 'Puedes enviar un recordatorio de pago al padre de familia por correo o WhatsApp.'
                };
            }
        }

        // H) Menú Lateral (Sidebar Navigation)
        const navLink = target.closest('.sidebar-nav a, nav a');
        if (navLink) {
            const navText = navLink.innerText.trim();
            const navDescMap = {
                'dashboard': { title: 'Menú: Panel Principal', desc: 'Resumen gráfico de cobros, asistencia escolar y avisos institucionales.', tip: 'Tu vista general del colegio al iniciar sesión.' },
                'colegios': { title: 'Menú: Gestión Global de Colegios', desc: 'Administración de escuelas dadas de alta en el SaaS TuCardex (solo Super Admin).', tip: 'Permite crear planteles, suspender o reactivar licencias.' },
                'alumnos': { title: 'Menú: Directorio de Alumnos', desc: 'Inscripciones, CURP oficial de 18 dígitos, credenciales escolares con QR y expedientes.', tip: 'Gestión completa de los estudiantes del colegio.' },
                'docentes': { title: 'Menú: Personal Docente', desc: 'Plantilla de profesores, especialidades, asignación de materias y horarios.', tip: 'Control de la planta de maestros del plantel.' },
                'grados': { title: 'Menú: Grados y Grupos', desc: 'Estructura académica: Preescolar, Primaria, Secundaria y asignación de salones.', tip: 'Organiza la estructura física y lectiva de la escuela.' },
                'materias': { title: 'Menú: Asignaturas y Materias', desc: 'Catálogo de materias conforme a la tira curricular oficial de la SEP.', tip: 'Define los planes de estudio del colegio.' },
                'calificaciones': { title: 'Menú: Calificaciones SEP', desc: 'Captura de notas en escala oficial base 10 (5.0 a 10.0), planillas y boletas oficial SEP.', tip: 'Emite boletas con Cédula Profesional del Director.' },
                'asistencias': { title: 'Menú: Control de Asistencia', desc: 'Pase de lista diario por grupo y cálculo del 80% mínimo de asistencia SEP.', tip: 'Verifica qué alumnos cumplen con el requisito de acreditación.' },
                'colegiaturas': { title: 'Menú: Colegiaturas y Cobranza', desc: 'Control de cuotas, transferencias SPEI, morosos y recibos foliados.', tip: 'Finanzas escolares adaptadas a pesos mexicanos ($ MXN).' },
                'facturación': { title: 'Menú: Facturación SAT CFDI 4.0', desc: 'Comprobantes fiscales con Complemento IEDU deducible de ISR para padres.', tip: 'Descarga directa de PDF con QR y XML timbrado.' },
                'biblioteca': { title: 'Menú: Biblioteca Escolar', desc: 'Control de libros, préstamos a estudiantes y fechas de devolución.', tip: 'Catalogación de ejemplares por código ISBN.' },
                'comunicados': { title: 'Menú: Avisos y Comunicados', desc: 'Publicación de circulares oficiales a la comunidad escolar.', tip: 'Envío de notificaciones por portal y correo.' },
                'bitácora': { title: 'Menú: Bitácora de Auditoría', desc: 'Registro detallado de acciones, fecha, hora y usuario que modificó datos.', tip: 'Máxima seguridad y trazabilidad en el colegio.' },
                'configuración': { title: 'Menú: Configuración Institucional', desc: 'Datos del colegio: CCT, RVOE, Director, Cédula Profesional y CLABE SPEI.', tip: 'Ajusta los membretes oficiales y cuentas de banco.' }
            };

            for (const k in navDescMap) {
                if (navText.toLowerCase().includes(k)) {
                    return navDescMap[k];
                }
            }
        }

        // I) Elementos del Navbar Superior
        if (target.closest('.navbar, .top-header')) {
            if (target.closest('.dropdown') && target.innerText.includes('Colegio')) {
                return {
                    title: 'Control: Selector de Colegio Activo',
                    desc: 'Muestra la institución educativa en la que estás operando actualmente.',
                    tip: 'Permite alternar entre planteles escolares si administras varios colegios.'
                };
            }
            if (target.closest('.dropdown-notifications') || target.querySelector('.bi-bell')) {
                return {
                    title: 'Control: Notificaciones del Sistema',
                    desc: 'Avisos automáticos sobre pagos registrados, timbrados SAT o comunicados institucionales.',
                    tip: 'Mantente al día con las actividades recientes de la escuela.'
                };
            }
            if (target.closest('.dropdown-user') || target.querySelector('.bi-person-circle')) {
                return {
                    title: 'Control: Menú de Usuario y Perfil',
                    desc: 'Acceso a tu cuenta personal, cambio de contraseña institucional y cierre seguro de sesión.',
                    tip: 'Cierra tu sesión al terminar de usar equipos compartidos.'
                };
            }
        }

        return null;
    }

    // 4. Inicialización del Asistente y Modo Explorador
    const fab = document.getElementById('cardex-fab');
    const panel = document.getElementById('cardex-panel');
    const btnClose = document.getElementById('btnClosePanel');
    const btnMin = document.getElementById('btnMinimizePanel');
    const btnInspectBanner = document.getElementById('btnBannerInspect');
    const btnInspectPanel = document.getElementById('btnToggleInspectFromPanel');
    const inspectorBar = document.getElementById('cardex-inspector-bar');
    const btnExitInspector = document.getElementById('btnExitInspector');
    const tooltip = document.getElementById('cardex-inspector-tooltip');
    const ttTitle = document.getElementById('cardex-tt-title');
    const ttDesc = document.getElementById('cardex-tt-desc');
    const ttTip = document.getElementById('cardex-tt-tip');
    const switchPersist = document.getElementById('switchAssistantPersist');
    const searchInput = document.getElementById('cardex-search-input');
    const searchResults = document.getElementById('cardex-search-results');
    const contextualContent = document.getElementById('cardex-contextual-content');
    const btnClearSearch = document.getElementById('btnClearSearch');

    let isInspectorActive = false;
    let hoveredElement = null;

    // Detectar módulo actual por URL
    function getCurrentModule() {
        const path = window.location.pathname;
        if (path.includes('facturacion')) return 'facturacion';
        if (path.includes('grades') || path.includes('notas')) return 'grades';
        if (path.includes('attendances') || path.includes('asistencias')) return 'attendances';
        if (path.includes('payments') || path.includes('pagos')) return 'payments';
        if (path.includes('students') || path.includes('estudiantes') || path.includes('alumnos')) return 'students';
        if (path.includes('settings') || path.includes('configuracion')) return 'settings';
        if (path.includes('colegios') || path.includes('schools')) return 'schools';
        return 'default';
    }

    // Cargar contenido contextual
    function loadContext() {
        const modKey = getCurrentModule();
        const mod = knowledgeBase[modKey] || knowledgeBase['default'];

        document.getElementById('cardex-page-name').textContent = mod.name;
        document.getElementById('cardex-page-summary').textContent = mod.summary;

        const stepsList = document.getElementById('cardex-quick-steps');
        stepsList.innerHTML = '';
        mod.steps.forEach(st => {
            const li = document.createElement('li');
            li.innerHTML = `<i class="bi bi-check2-circle"></i> <span>${st}</span>`;
            stepsList.appendChild(li);
        });

        const faqsContainer = document.getElementById('cardex-faqs');
        faqsContainer.innerHTML = '';
        mod.faqs.forEach((faq, idx) => {
            const item = document.createElement('div');
            item.className = 'cardex-faq-item';
            item.innerHTML = `
                <div class="cardex-faq-q">
                    <span>${faq.q}</span>
                    <i class="bi bi-chevron-down small text-muted"></i>
                </div>
                <div class="cardex-faq-a">${faq.a}</div>
            `;
            item.querySelector('.cardex-faq-q').addEventListener('click', () => {
                item.classList.toggle('active');
            });
            faqsContainer.appendChild(item);
        });
    }

    // Toggle Panel
    function togglePanel(show) {
        if (show === undefined) {
            panel.classList.toggle('d-none');
        } else if (show) {
            panel.classList.remove('d-none');
        } else {
            panel.classList.add('d-none');
        }
    }

    // MODO EXPLORADOR (Hover con el Mouse)
    function startInspector() {
        isInspectorActive = true;
        togglePanel(false);
        inspectorBar.classList.remove('d-none');
        document.body.style.cursor = 'help';
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('keydown', onKeyDown);
    }

    function stopInspector() {
        isInspectorActive = false;
        inspectorBar.classList.add('d-none');
        tooltip.classList.add('d-none');
        document.body.style.cursor = 'default';
        if (hoveredElement) {
            hoveredElement.classList.remove('cardex-inspected-element');
            hoveredElement = null;
        }
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('keydown', onKeyDown);
    }

    function onKeyDown(e) {
        if (e.key === 'Escape' && isInspectorActive) {
            stopInspector();
            togglePanel(true);
        }
    }

    function onMouseMove(e) {
        if (!isInspectorActive) return;

        // Evitar inspeccionar la barra, el panel o el tooltip
        if (e.target.closest('#cardex-inspector-bar') || e.target.closest('#cardex-inspector-tooltip') || e.target.closest('#cardex-panel') || e.target.closest('#cardex-fab')) {
            return;
        }

        // Buscar el elemento interactivo específico más relevante
        const target = e.target.closest('th, td, button, a, input, select, textarea, .badge, .badge-soft, .stat-card, .sidebar-nav li, .nav-item');
        if (!target) {
            tooltip.classList.add('d-none');
            if (hoveredElement) {
                hoveredElement.classList.remove('cardex-inspected-element');
                hoveredElement = null;
            }
            return;
        }

        if (hoveredElement !== target) {
            if (hoveredElement) hoveredElement.classList.remove('cardex-inspected-element');
            hoveredElement = target;
            hoveredElement.classList.add('cardex-inspected-element');
        }

        // Resolver significado semántico específico
        const info = resolveElementSemantics(target);

        if (info) {
            ttTitle.textContent = info.title;
            ttDesc.textContent = info.desc;
            ttTip.textContent = info.tip.startsWith('💡') ? info.tip : `💡 ${info.tip}`;
        } else {
            // Manejo específico según rol real del elemento (evitar respuestas redundantes genéricas)
            const text = target.innerText?.trim().slice(0, 32) || target.getAttribute('placeholder') || '';
            if (target.tagName === 'A') {
                ttTitle.textContent = `Enlace: ${text || 'Navegación'}`;
                ttDesc.textContent = 'Accede directamente a este recurso o pantalla del sistema escolar.';
                ttTip.textContent = '💡 Haz clic sobre el enlace para abrir la sección.';
            } else if (target.tagName === 'BUTTON') {
                ttTitle.textContent = `Botón: ${text || 'Acción'}`;
                ttDesc.textContent = 'Ejecuta una operación sobre el registro escolar o formulario actual.';
                ttTip.textContent = '💡 Haz clic para procesar la acción correspondiente.';
            } else if (target.matches('input, select, textarea')) {
                ttTitle.textContent = `Campo de Captura: ${target.getAttribute('name') || 'Dato'}`;
                ttDesc.textContent = 'Permite ingresar o seleccionar información requerida para el registro.';
                ttTip.textContent = '💡 Revisa que el formato cumpla con los lineamientos del colegio.';
            } else {
                ttTitle.textContent = `Elemento Escolar: ${text}`;
                ttDesc.textContent = 'Información registrada en este módulo de la plataforma TuCardex.';
                ttTip.textContent = '💡 Consulta o interactúa con este dato según tus permisos.';
            }
        }

        // Posicionar tooltip cerca del cursor sin salirse de la pantalla
        let x = e.clientX + 16;
        let y = e.clientY + 16;
        if (x + 320 > window.innerWidth) x = e.clientX - 330;
        if (y + 160 > window.innerHeight) y = e.clientY - 175;

        tooltip.style.left = `${Math.max(12, x)}px`;
        tooltip.style.top = `${Math.max(60, y)}px`;
        tooltip.classList.remove('d-none');
    }

    // Buscador interactivo
    function handleSearch(val) {
        const query = val.toLowerCase().trim();
        if (!query) {
            searchResults.classList.add('d-none');
            contextualContent.classList.remove('d-none');
            btnClearSearch.classList.add('d-none');
            return;
        }

        btnClearSearch.classList.remove('d-none');
        searchResults.classList.remove('d-none');
        contextualContent.classList.add('d-none');

        const allFaqs = [];
        Object.values(knowledgeBase).forEach(mod => {
            mod.faqs.forEach(f => allFaqs.push(f));
        });

        const matches = allFaqs.filter(f => f.q.toLowerCase().includes(query) || f.a.toLowerCase().includes(query));

        if (matches.length === 0) {
            searchResults.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-question-circle fs-3 d-block mb-2"></i>
                    <div style="font-size:12px">No encontré una respuesta exacta para "${query}".</div>
                    <small style="font-size:11px">Prueba buscando "factura", "iedu", "notas", "boleta", "curp" o "asistencia".</small>
                </div>
            `;
            return;
        }

        searchResults.innerHTML = matches.map(m => `
            <div class="cardex-card mb-2">
                <div class="fw-bold text-dark mb-1" style="font-size:12px"><i class="bi bi-lightbulb-fill text-warning me-1"></i>${m.q}</div>
                <div style="font-size:11.5px;color:#4b5563;line-height:1.4">${m.a}</div>
            </div>
        `).join('');
    }

    // Eventos
    fab.addEventListener('click', () => togglePanel());
    btnClose.addEventListener('click', () => togglePanel(false));
    btnMin.addEventListener('click', () => togglePanel(false));

    btnInspectBanner.addEventListener('click', startInspector);
    btnInspectPanel.addEventListener('click', startInspector);
    btnExitInspector.addEventListener('click', () => {
        stopInspector();
        togglePanel(true);
    });

    searchInput.addEventListener('input', (e) => handleSearch(e.target.value));
    btnClearSearch.addEventListener('click', () => {
        searchInput.value = '';
        handleSearch('');
    });

    // Estado persistente
    const isEnabled = localStorage.getItem('cardex_assistant_enabled') !== 'false';
    switchPersist.checked = isEnabled;
    if (!isEnabled) {
        fab.classList.add('d-none');
    }

    switchPersist.addEventListener('change', (e) => {
        const enabled = e.target.checked;
        localStorage.setItem('cardex_assistant_enabled', enabled ? 'true' : 'false');
        if (!enabled) {
            togglePanel(false);
            fab.classList.add('d-none');
        }
    });

    // Exponer globalmente para que el botón del navbar lo pueda abrir
    window.openCardexAssistant = function() {
        localStorage.setItem('cardex_assistant_enabled', 'true');
        switchPersist.checked = true;
        fab.classList.remove('d-none');
        togglePanel(true);
    };

    // Cargar contenido al iniciar
    loadContext();

})();
</script>
