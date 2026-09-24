@php $u = auth()->user(); @endphp
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        @if(optional($appSettings)->logo_url)
            <img src="{{ $appSettings->logo_url }}" alt="logo" style="width:34px;height:34px;border-radius:10px;object-fit:cover">
        @else
            <i class="bi bi-mortarboard-fill"></i>
        @endif
        {{ $appSettings->school_name ?? 'TuCardex' }}
    </div>

    <div class="sidebar-user">
        @if($u->avatar_url)<img src="{{ $u->avatar_url }}" class="avatar" style="object-fit:cover">@else<div class="avatar">{{ $u->initials() }}</div>@endif
        <div class="meta">
            <strong>{{ $u->name }}</strong>
            <span><span class="status-dot"></span>{{ optional($u->role)->name ?? 'Usuario' }}</span>
        </div>
    </div>

    @php
        $isAcad = $u->hasAnyRole(['admin','secretaria']);
        $isDaily = $u->hasAnyRole(['admin','secretaria','docente']);
        $isParent = $u->hasRole('padre');
    @endphp

    @if($u->isSuperAdmin())
        <div class="nav-label">Plataforma</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="{{ route('schools.index') }}" class="{{ request()->routeIs('schools.*') ? 'active' : '' }}"><i class="bi bi-buildings"></i> Colegios</a></li>
        </ul>
    @else
        @php $unread = \App\Models\Message::unreadCountFor($u->id); @endphp
        <div class="nav-label">Principal</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }}"><i class="bi bi-chat-dots"></i> Mensajes
                @if($unread)<span class="badge rounded-pill bg-danger ms-auto">{{ $unread }}</span>@endif</a></li>
        </ul>
    @endif

        @if($isParent)
        @php
            $currentChild = null;
            if (session()->has('parent_selected_student_id')) {
                $currentChild = $u->children->firstWhere('id', session('parent_selected_student_id'));
            }
            $currentChild = $currentChild ?? $u->children->first();
        @endphp
        @if($currentChild)
        <div class="nav-label">Expediente Alumno</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('students.boletin', $currentChild) }}" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Boleta Oficial</a></li>
            <li><a href="{{ route('students.estadoCuenta', $currentChild) }}" target="_blank"><i class="bi bi-receipt"></i> Estado de Cuenta</a></li>
        </ul>
        @endif
    @endif

    @if($isAcad)
    <div class="nav-label">Secretaría y Control Escolar</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('secretaria.index') }}" class="{{ request()->routeIs('secretaria.index') ? 'active' : '' }}"><i class="bi bi-person-workspace"></i> Panel Secretaría</a></li>
        <li><a href="{{ route('secretaria.admisiones.index') }}" class="{{ request()->routeIs('secretaria.admisiones.*') ? 'active' : '' }}"><i class="bi bi-person-plus-fill"></i> Admisiones / Fichas</a></li>
        <li><a href="{{ route('secretaria.credenciales') }}" class="{{ request()->routeIs('secretaria.credenciales*') ? 'active' : '' }}"><i class="bi bi-person-badge"></i> Credenciales Escolares</a></li>
        <li><a href="{{ route('secretaria.constancias') }}" class="{{ request()->routeIs('secretaria.constancias*') ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i> Constancias Oficiales</a></li>
        <li><a href="{{ route('secretaria.oficios') }}" class="{{ request()->routeIs('secretaria.oficios*') ? 'active' : '' }}"><i class="bi bi-envelope-paper"></i> Citatorios y Oficios</a></li>
    </ul>

    <div class="nav-label">Académico</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i> Estudiantes</a></li>
        <li><a href="{{ route('teachers.index') }}" class="{{ request()->routeIs('teachers.*') ? 'active' : '' }}"><i class="bi bi-person-badge"></i> Docentes</a></li>
        <li><a href="{{ route('courses.index') }}" class="{{ request()->routeIs('courses.*') ? 'active' : '' }}"><i class="bi bi-collection"></i> Cursos / Grados</a></li>
        <li><a href="{{ route('subjects.index') }}" class="{{ request()->routeIs('subjects.*') ? 'active' : '' }}"><i class="bi bi-journal-bookmark"></i> Materias</a></li>
        <li><a href="{{ route('enrollments.index') }}" class="{{ request()->routeIs('enrollments.*') ? 'active' : '' }}"><i class="bi bi-card-checklist"></i> Matrículas</a></li>
        <li><a href="{{ route('promotions.index') }}" class="{{ request()->routeIs('promotions.*') ? 'active' : '' }}"><i class="bi bi-arrow-up-circle"></i> Promoción de año</a></li>
    </ul>
    @endif

    @if($isDaily)
    <div class="nav-label">Operación diaria</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('attendances.index') }}" class="{{ request()->routeIs('attendances.*') ? 'active' : '' }}"><i class="bi bi-calendar2-check"></i> Asistencia</a></li>
        <li><a href="{{ route('grades.index') }}" class="{{ request()->routeIs('grades.*') ? 'active' : '' }}"><i class="bi bi-clipboard-data"></i> Calificaciones</a></li>
        <li><a href="{{ route('assignments.index') }}" class="{{ request()->routeIs('assignments.*') ? 'active' : '' }}"><i class="bi bi-journal-text"></i> Tareas</a></li>
        <li><a href="{{ route('schedules.index') }}" class="{{ request()->routeIs('schedules.*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Horarios</a></li>
        <li><a href="{{ route('incidents.index') }}" class="{{ request()->routeIs('incidents.*') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i> Disciplina</a></li>
        <li><a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}"><i class="bi bi-calendar-event"></i> Calendario</a></li>
    </ul>
    @endif

        @if($isParent)
        @php
            $currentChild = null;
            if (session()->has('parent_selected_student_id')) {
                $currentChild = $u->children->firstWhere('id', session('parent_selected_student_id'));
            }
            $currentChild = $currentChild ?? $u->children->first();
        @endphp
        @if($currentChild)
        <div class="nav-label">Expediente Alumno</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('students.boletin', $currentChild) }}" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Boleta Oficial</a></li>
            <li><a href="{{ route('students.estadoCuenta', $currentChild) }}" target="_blank"><i class="bi bi-receipt"></i> Estado de Cuenta</a></li>
        </ul>
        @endif
    @endif

    @php
        $school = $u->school;
        $hasBilling = $school ? $school->hasFeature('billing') : true;
    @endphp

    @if($isAcad && $hasBilling)
    <div class="nav-label">Finanzas</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('payments.gateways') }}" class="{{ request()->routeIs('payments.gateways') ? 'active' : '' }}"><i class="bi bi-credit-card-2-front"></i> Pasarelas de Pago</a></li>
        @if($u->isAdmin() || $u->isSecretaria())
        <li><a href="{{ route('configuracion.whatsapp') }}" class="{{ request()->routeIs('configuracion.whatsapp') ? 'active' : '' }}"><i class="bi bi-whatsapp"></i> WhatsApp & Cobranza</a></li>
        @endif
        <li><a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i> Pagos / Colegiaturas</a></li>
        @if(optional(\App\Models\ElectronicBillingSetting::current())->enabled)
        <li><a href="{{ route('facturacion.index') }}" class="{{ request()->routeIs('facturacion.index') || request()->routeIs('facturacion.show') ? 'active' : '' }}"><i class="bi bi-receipt-cutoff"></i> Facturación Electrónica</a></li>
        @if($u->isAdmin())
            <li><a href="{{ route('facturacion.configuracion') }}" class="{{ request()->routeIs('facturacion.configuracion') ? 'active' : '' }}"><i class="bi bi-file-earmark-medical"></i> Facturación SAT (CFDI 4.0)</a></li>
        @endif
        @endif
    </ul>

    <div class="nav-label">Biblioteca</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('books.index') }}" class="{{ request()->routeIs('books.*') ? 'active' : '' }}"><i class="bi bi-book"></i> Catálogo</a></li>
        <li><a href="{{ route('loans.index') }}" class="{{ request()->routeIs('loans.*') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i> Préstamos</a></li>
    </ul>
    @endif

    @if($isDaily)
    <div class="nav-label">Comunicación</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}"><i class="bi bi-megaphone"></i> Comunicados</a></li>
    </ul>

    <div class="nav-label">Administración</div>
    <ul class="sidebar-nav">
        <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="bi bi-bar-chart-line"></i> Reportes</a></li>
        @if($u->isAdmin())
            <li><a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-shield-lock"></i> Usuarios y Roles</a></li>
            <li><a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Bitácora</a></li>
            <li><a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="bi bi-gear"></i> Configuración</a></li>
        @endif
    </ul>
    @endif

    <ul class="sidebar-nav" style="margin-top:auto">
        <li>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" style="background:none;border:none;width:100%;text-align:left;color:var(--sidebar-link);padding:11px 18px;display:flex;align-items:center;gap:12px;font-size:13.5px;border-left:3px solid transparent;cursor:pointer">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </button>
            </form>
        </li>
    </ul>
</aside>
