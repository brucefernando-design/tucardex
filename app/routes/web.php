<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSubjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ElectronicBillingController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ParentPaymentController;
use App\Http\Controllers\Api\MercadoPagoWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SecretariaController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('welcome'))->name('home');

// Autenticación
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    // Registro / onboarding de nuevos colegios
    Route::get('registro', [RegisterController::class, 'show'])->name('register');
    Route::post('registro', [RegisterController::class, 'register'])->middleware('throttle:5,1');

    // Recuperación de contraseña
    Route::get('olvide-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('olvide-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('restablecer-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('restablecer-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Aplicación (requiere sesión)
Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Recibo oficial en PDF (accesible para padres, alumnos y administración)
    Route::get('payments/{payment}/recibo', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // Pasarela de Pagos en Línea para Familias (Mercado Pago / SPEI)
    Route::get('pagos/{payment}/checkout', [ParentPaymentController::class, 'checkout'])->name('parent.payments.checkout');
    Route::post('pagos/{payment}/mercadopago', [ParentPaymentController::class, 'mercadoPago'])->name('parent.payments.mercadopago');
    Route::post('pagos/{payment}/simular', [ParentPaymentController::class, 'simulate'])->name('parent.payments.simulate');
    Route::get('pagos/{payment}/retorno', [ParentPaymentController::class, 'returnCallback'])->name('parent.payments.return');
    Route::get('pagos/{payment}/exito', [ParentPaymentController::class, 'success'])->name('parent.payments.success');
    Route::post('pagos/{payment}/comprobante', [ParentPaymentController::class, 'uploadSpeiProof'])->name('parent.payments.voucher');
    Route::get('suscripcion/expirada', [SubscriptionController::class, 'expired'])->name('subscription.expired');

    // Perfil
    Route::get('perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('perfil', [ProfileController::class, 'update'])->name('profile.update');

    // Notificaciones
    Route::get('notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notificaciones/{notification}/leer', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('notificaciones/leer-todas', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::delete('notificaciones/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Mensajería interna (todos los usuarios)
    Route::get('mensajes', [MessageController::class, 'index'])->name('messages.index');
    Route::get('mensajes/enviados', [MessageController::class, 'sent'])->name('messages.sent');
    Route::get('mensajes/redactar', [MessageController::class, 'create'])->name('messages.create');
    Route::post('mensajes', [MessageController::class, 'store'])->name('messages.store');
    Route::get('mensajes/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::delete('mensajes/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    // Entrega de tarea por el estudiante
    Route::post('tareas/{assignment}/entregar', [SubmissionController::class, 'store'])->name('submissions.store');

    // Boletín de notas (PDF) — disponible para roles con acceso a su ficha
    Route::get('students/{student}/boletin', [ReportCardController::class, 'pdf'])->name('students.boletin');
    Route::get('students/{student}/constancia', [StudentDocumentController::class, 'constancia'])->name('students.constancia');
    Route::get('students/{student}/carnet', [StudentDocumentController::class, 'carnet'])->name('students.carnet');
    Route::get('students/{student}/estado-cuenta', [StudentDocumentController::class, 'estadoCuenta'])->name('students.estadoCuenta');
    // Descarga de CFDI 4.0 accesible para alumnos, tutores y personal autenticado
    Route::get('facturacion/{invoice}/pdf', [ElectronicBillingController::class, 'pdf'])->name('facturacion.pdf');
    Route::get('facturacion/{invoice}/xml', [ElectronicBillingController::class, 'descargarXml'])->name('facturacion.xml');

    // ===== Gestión académica y administrativa: Administrador + Secretaría =====
    Route::middleware('role:admin,secretaria')->group(function () {
        // Módulo de Secretaría y Control Escolar
        Route::prefix('secretaria')->name('secretaria.')->group(function () {
            Route::get('/', [SecretariaController::class, 'index'])->name('index');
            Route::get('credenciales', [SecretariaController::class, 'credenciales'])->name('credenciales');
            Route::get('credenciales/grupo/{course}', [SecretariaController::class, 'descargarCredencialesGrupo'])->name('credenciales.grupo');
            Route::get('credencial/{student}', [SecretariaController::class, 'descargarCredencial'])->name('credencial.descargar');
            Route::get('constancias', [SecretariaController::class, 'constancias'])->name('constancias');
            Route::get('constancia/{student}', [SecretariaController::class, 'descargarConstancia'])->name('constancia.descargar');
            Route::get('buena-conducta/{student}', [SecretariaController::class, 'descargarBuenaConducta'])->name('buena-conducta.descargar');
            Route::get('no-adeudo/{student}', [SecretariaController::class, 'descargarNoAdeudo'])->name('no-adeudo.descargar');
            Route::get('kardex/{student}', [SecretariaController::class, 'descargarKardex'])->name('kardex.descargar');
            Route::get('ficha/{student}', [SecretariaController::class, 'descargarFichaMatricula'])->name('ficha.descargar');
            Route::get('oficios', [SecretariaController::class, 'oficios'])->name('oficios');
            Route::post('oficio/descargar', [SecretariaController::class, 'descargarOficio'])->name('oficio.descargar');
            Route::post('oficio/enviar', [SecretariaController::class, 'enviarOficio'])->name('oficio.enviar');
        });

        // Importación masiva de estudiantes (antes del resource para no chocar con students/{student})
        Route::get('students/importar', [StudentController::class, 'importForm'])->name('students.import.form');
        Route::post('students/importar', [StudentController::class, 'import'])->name('students.import');
        Route::get('students/plantilla', [StudentController::class, 'template'])->name('students.template');
        Route::resource('students', StudentController::class);
        Route::resource('teachers', TeacherController::class);

        Route::get('courses/{course}/acta', [ReportCardController::class, 'courseSheet'])->name('courses.acta');
        Route::get('courses/{course}/materias', [CourseSubjectController::class, 'index'])->name('courses.academic.index');
        Route::post('courses/{course}/materias', [CourseSubjectController::class, 'store'])->name('courses.academic.store');
        Route::put('courses/{course}/materias/{subject}', [CourseSubjectController::class, 'update'])->name('courses.academic.update');
        Route::delete('courses/{course}/materias/{subject}', [CourseSubjectController::class, 'destroy'])->name('courses.academic.destroy');
        Route::resource('courses', CourseController::class);

        Route::resource('subjects', SubjectController::class)->except('show');
        Route::resource('enrollments', EnrollmentController::class)->only(['index', 'store', 'destroy']);
        Route::get('promocion', [PromotionController::class, 'index'])->name('promotions.index');
        Route::post('promocion', [PromotionController::class, 'store'])->name('promotions.store');

        // Finanzas y Pasarelas de Pago México
        Route::get('payments/pasarelas', [PaymentController::class, 'gateways'])->name('payments.gateways');
        Route::post('payments/pasarelas', [PaymentController::class, 'saveGateways'])->name('payments.gateways.save');
        Route::get('payments/morosos', [PaymentController::class, 'defaulters'])->name('payments.defaulters');
        Route::post('payments/generar', [PaymentController::class, 'generate'])->name('payments.generate');
        Route::resource('payments', PaymentController::class)->only(['index', 'store', 'destroy']);
        Route::patch('payments/{payment}/pagar', [PaymentController::class, 'markPaid'])->name('payments.markPaid');
        Route::get('payments/{payment}/recibo', [PaymentController::class, 'receipt'])->name('payments.receipt');

        // Facturación Electrónica (SUNAT · Perú)
        Route::get('facturacion', [ElectronicBillingController::class, 'index'])->name('facturacion.index');
        Route::post('facturacion/pago/{payment}/emitir', [ElectronicBillingController::class, 'emitirDesdePago'])->name('facturacion.emitir');
        Route::post('facturacion/{invoice}/reenviar', [ElectronicBillingController::class, 'reenviar'])->name('facturacion.reenviar');
        Route::post('facturacion/{invoice}/anular', [ElectronicBillingController::class, 'anular'])->name('facturacion.anular');
        Route::post('facturacion/{invoice}/nota-credito', [ElectronicBillingController::class, 'notaCredito'])->name('facturacion.nc');

        // Resúmenes diarios de boletas
        Route::get('facturacion-resumenes', [ElectronicBillingController::class, 'resumenes'])->name('facturacion.resumenes');
        Route::post('facturacion-resumenes/generar', [ElectronicBillingController::class, 'generarResumen'])->name('facturacion.resumenes.generar');
        Route::post('facturacion-resumenes/{summary}/consultar', [ElectronicBillingController::class, 'consultarResumen'])->name('facturacion.resumenes.consultar');
        Route::get('facturacion/{invoice}/detalle', [ElectronicBillingController::class, 'show'])->name('facturacion.show');
        Route::get('facturacion/{invoice}/cdr', [ElectronicBillingController::class, 'descargarCdr'])->name('facturacion.cdr');

        // Exportaciones
        Route::get('export/students', [StudentController::class, 'export'])->name('students.export');
        Route::get('export/payments', [PaymentController::class, 'export'])->name('payments.export');

        // Biblioteca
        Route::resource('books', BookController::class)->except('show');
        Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
        Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
        Route::patch('loans/{loan}/devolver', [LoanController::class, 'returnBook'])->name('loans.return');
        Route::delete('loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');
    });

    // ===== Gestión diaria y comunicación: Admin + Secretaría + Docente =====
    Route::middleware('role:admin,secretaria,docente')->group(function () {
        Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');
        Route::get('attendances/reporte', [AttendanceController::class, 'report'])->name('attendances.report');
        Route::get('attendances/reporte/pdf', [AttendanceController::class, 'reportPdf'])->name('attendances.report.pdf');
        Route::get('grades/masivo', [GradeController::class, 'batch'])->name('grades.batch');
        Route::post('grades/masivo', [GradeController::class, 'batchStore'])->name('grades.batchStore');
        Route::resource('grades', GradeController::class)->only(['index', 'store', 'destroy']);
        Route::resource('schedules', ScheduleController::class)->only(['index', 'store', 'destroy']);
        Route::resource('events', EventController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('assignments', AssignmentController::class)->except('show');
        Route::get('assignments/{assignment}/entregas', [SubmissionController::class, 'index'])->name('submissions.index');
        Route::put('submissions/{submission}/revisar', [SubmissionController::class, 'review'])->name('submissions.review');
        Route::get('incidents', [IncidentController::class, 'index'])->name('incidents.index');
        Route::post('incidents', [IncidentController::class, 'store'])->name('incidents.store');
        Route::delete('incidents/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');
        Route::resource('announcements', AnnouncementController::class)->except('show');
        Route::post('announcements/{announcement}/reenviar', [AnnouncementController::class, 'resend'])->name('announcements.resend');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // ===== Super-administrador de la plataforma =====
    Route::middleware('role:superadmin')->group(function () {
        Route::get('plataforma/colegios', [SchoolController::class, 'index'])->name('schools.index');
        Route::post('plataforma/colegios', [SchoolController::class, 'store'])->name('schools.store');
        Route::get('plataforma/colegios/{school}', [SchoolController::class, 'show'])->name('schools.show');
        Route::put('plataforma/colegios/{school}', [SchoolController::class, 'update'])->name('schools.update');
        Route::delete('plataforma/colegios/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');
    });

    // ===== Solo Administrador =====
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

        // Configuración de Facturación Electrónica
        Route::get('facturacion/configuracion', [ElectronicBillingController::class, 'configuracion'])->name('facturacion.configuracion');
        Route::post('facturacion/configuracion', [ElectronicBillingController::class, 'guardar'])->name('facturacion.guardar');
        Route::post('facturacion/probar', [ElectronicBillingController::class, 'probar'])->name('facturacion.probar');
        Route::get('bitacora', [AuditLogController::class, 'index'])->name('audit.index');
    });
});

// Webhooks Pasarelas (sin autenticación de sesión, verificados por firma/token)
Route::post('api/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])->name('webhooks.mercadopago');
