@php 
    $u = auth()->user(); 
    $school = $u->school;
    $schoolName = $school?->name ?? $appSettings->school_name ?? 'TuCardex';
@endphp
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        @if(optional($appSettings)->logo_url)
            <img src="{{ $appSettings->logo_url }}" alt="logo" style="width:34px;height:34px;border-radius:10px;object-fit:cover">
        @else
            <div style="width:34px;height:34px;border-radius:10px;background:linear-gradient(135deg,var(--brand-2),var(--brand));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;box-shadow:0 4px 12px rgba(34,197,94,.35)">
                <i class="bi bi-mortarboard-fill" style="box-shadow:none;background:none;width:auto;height:auto;border-radius:0;font-size:18px;"></i>
            </div>
        @endif
        <div class="d-flex flex-column" style="min-width:0; overflow:hidden;">
            <span style="font-weight:700;font-size:14.5px;color:#fff;line-height:1.2;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;" title="{{ $schoolName }}">
                {{ $schoolName }}
            </span>
            <span style="font-size:11px;color:var(--sidebar-muted);font-weight:500;">Panel Escolar</span>
        </div>
    </div>

    <div class="sidebar-user">
        @if($u->avatar_url)
            <img src="{{ $u->avatar_url }}" class="avatar" style="object-fit:cover">
        @else
            <div class="avatar">{{ $u->initials() }}</div>
        @endif
        <div class="meta">
            <strong>{{ $u->name }}</strong>
            <span><span class="status-dot"></span>{{ optional($u->role)->name ?? 'Usuario' }}</span>
        </div>
    </div>

    @php
        $isAdmin = $u->isAdmin();
        $isSecretaria = $u->hasRole('secretaria');
        $isAcad = $u->hasAnyRole(['admin','secretaria']);
        $isTeacher = $u->hasRole('docente');
        $isParent = $u->hasRole('padre');
        $isSuperAdmin = $u->isSuperAdmin();
        $unread = \App\Models\Message::unreadCountFor($u->id);
    @endphp

    {{-- SUPERADMIN --}}
    @if($isSuperAdmin)
        <div class="nav-label">PLATAFORMA</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Inicio Global</a></li>
            <li><a href="{{ route('schools.index') }}" class="{{ request()->routeIs('schools.*') ? 'active' : '' }}"><i class="bi bi-buildings"></i> Colegios</a></li>
        </ul>

    {{-- PADRE / TUTOR --}}
    @elseif($isParent)
        @php
            $currentChild = null;
            if (session()->has('parent_selected_student_id')) {
                $currentChild = $u->children->firstWhere('id', session('parent_selected_student_id'));
            }
            $currentChild = $currentChild ?? $u->children->first();
        @endphp
        <div class="nav-label">PRINCIPAL</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-house-door"></i> Mi Inicio</a></li>
            <li><a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }}"><i class="bi bi-chat-dots"></i> Mensajes
                @if($unread)<span class="badge rounded-pill bg-danger ms-auto">{{ $unread }}</span>@endif</a></li>
        </ul>

        @if($currentChild)
        <div class="nav-label">EXPEDIENTE ESCOLAR</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('students.boletin', $currentChild) }}" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Boleta Oficial</a></li>
            <li><a href="{{ route('students.estadoCuenta', $currentChild) }}" target="_blank"><i class="bi bi-receipt"></i> Estado de Cuenta</a></li>
            <li><a href="{{ route('students.carnet', $currentChild) }}" target="_blank"><i class="bi bi-person-vcard"></i> Credencial Escolar</a></li>
            <li><a href="{{ route('students.constancia', $currentChild) }}" target="_blank"><i class="bi bi-file-text"></i> Constancia de Estudios</a></li>
        </ul>
        @endif

    {{-- DOCENTE --}}
    @elseif($isTeacher)
        <div class="nav-label">PRINCIPAL</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li><a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }}"><i class="bi bi-chat-dots"></i> Mensajes
                @if($unread)<span class="badge rounded-pill bg-danger ms-auto">{{ $unread }}</span>@endif</a></li>
        </ul>

        <div class="nav-label">ACADÉMICO</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('attendances.index') }}" class="{{ request()->routeIs('attendances.*') ? 'active' : '' }}"><i class="bi bi-calendar2-check"></i> Pase de lista</a></li>
            <li><a href="{{ route('grades.index') }}" class="{{ request()->routeIs('grades.*') ? 'active' : '' }}"><i class="bi bi-clipboard-data"></i> Calificaciones</a></li>
            <li><a href="{{ route('assignments.index') }}" class="{{ request()->routeIs('assignments.*') ? 'active' : '' }}"><i class="bi bi-journal-text"></i> Tareas y actividades</a></li>
            <li><a href="{{ route('schedules.index') }}" class="{{ request()->routeIs('schedules.*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Mi horario</a></li>
            <li><a href="{{ route('incidents.index') }}" class="{{ request()->routeIs('incidents.*') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i> Disciplina</a></li>
            <li><a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}"><i class="bi bi-calendar-event"></i> Calendario</a></li>
            <li><a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}"><i class="bi bi-megaphone"></i> Avisos escolares</a></li>
        </ul>

    {{-- ADMINISTRADOR & SECRETARÍA --}}
    @else
        <div class="nav-label">PRINCIPAL</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li><a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }}"><i class="bi bi-chat-dots"></i> Mensajes
                @if($unread)<span class="badge rounded-pill bg-danger ms-auto">{{ $unread }}</span>@endif</a></li>
        </ul>

        <div class="nav-label">SECRETARÍA</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') ? 'active' : '' }}"><i class="bi bi-people-fill"></i> Alumnos</a></li>
            <li><a href="{{ route('secretaria.admisiones.index') }}" class="{{ request()->routeIs('secretaria.admisiones.*') ? 'active' : '' }}"><i class="bi bi-person-plus-fill"></i> Admisiones / Fichas</a></li>
            <li><a href="{{ route('enrollments.index') }}" class="{{ request()->routeIs('enrollments.*') ? 'active' : '' }}"><i class="bi bi-card-checklist"></i> Inscripciones</a></li>
            <li><a href="{{ route('courses.index') }}" class="{{ request()->routeIs('courses.*') ? 'active' : '' }}"><i class="bi bi-collection"></i> Grados y Grupos</a></li>
            <li><a href="{{ route('attendances.index') }}" class="{{ request()->routeIs('attendances.*') ? 'active' : '' }}"><i class="bi bi-calendar2-check"></i> Pase de lista</a></li>
            <li><a href="{{ route('secretaria.index') }}" class="{{ request()->routeIs('secretaria.index') ? 'active' : '' }}"><i class="bi bi-person-workspace"></i> Control escolar</a></li>
            <li><a href="{{ route('secretaria.credenciales') }}" class="{{ request()->routeIs('secretaria.credenciales*') ? 'active' : '' }}"><i class="bi bi-person-badge"></i> Credenciales</a></li>
            <li><a href="{{ route('secretaria.constancias') }}" class="{{ request()->routeIs('secretaria.constancias*') ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i> Constancias</a></li>
            <li><a href="{{ route('secretaria.oficios') }}" class="{{ request()->routeIs('secretaria.oficios*') ? 'active' : '' }}"><i class="bi bi-envelope-paper"></i> Citatorios y oficios</a></li>
        </ul>

        <div class="nav-label">ACADÉMICO</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('grades.index') }}" class="{{ request()->routeIs('grades.*') ? 'active' : '' }}"><i class="bi bi-clipboard-data"></i> Calificaciones</a></li>
            <li><a href="{{ route('subjects.index') }}" class="{{ request()->routeIs('subjects.*') ? 'active' : '' }}"><i class="bi bi-journal-bookmark"></i> Materias</a></li>
            <li><a href="{{ route('teachers.index') }}" class="{{ request()->routeIs('teachers.*') ? 'active' : '' }}"><i class="bi bi-person-video3"></i> Docentes</a></li>
            <li><a href="{{ route('schedules.index') }}" class="{{ request()->routeIs('schedules.*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Horarios</a></li>
            <li><a href="{{ route('assignments.index') }}" class="{{ request()->routeIs('assignments.*') ? 'active' : '' }}"><i class="bi bi-journal-text"></i> Tareas</a></li>
            <li><a href="{{ route('incidents.index') }}" class="{{ request()->routeIs('incidents.*') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i> Disciplina</a></li>
            <li><a href="{{ route('promotions.index') }}" class="{{ request()->routeIs('promotions.*') ? 'active' : '' }}"><i class="bi bi-arrow-up-circle"></i> Promoción de grado</a></li>
            <li><a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'active' : '' }}"><i class="bi bi-calendar-event"></i> Calendario escolar</a></li>
        </ul>

        @php
            $hasBilling = $school ? $school->hasFeature('billing') : true;
        @endphp
        @if($hasBilling)
        <div class="nav-label">COBRANZA</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i> Colegiaturas</a></li>
            <li><a href="{{ route('configuracion.whatsapp') }}" class="{{ request()->routeIs('configuracion.whatsapp') ? 'active' : '' }}"><i class="bi bi-whatsapp"></i> WhatsApp & Cobranza</a></li>
            <li><a href="{{ route('payments.gateways') }}" class="{{ request()->routeIs('payments.gateways') ? 'active' : '' }}"><i class="bi bi-credit-card-2-front"></i> Pasarelas de pago</a></li>
            @if(optional(\App\Models\ElectronicBillingSetting::current())->enabled)
                <li><a href="{{ route('facturacion.index') }}" class="{{ request()->routeIs('facturacion.index') || request()->routeIs('facturacion.show') ? 'active' : '' }}"><i class="bi bi-receipt-cutoff"></i> Facturación CFDI 4.0</a></li>
            @endif
            <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="bi bi-bar-chart-line"></i> Reportes financieros</a></li>
        </ul>
        @endif

        <div class="nav-label">CONFIGURACIÓN</div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('announcements.index') }}" class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}"><i class="bi bi-megaphone"></i> Comunicados</a></li>
            @if($isAdmin)
                <li><a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="bi bi-gear"></i> Colegio y ciclos</a></li>
                <li><a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-shield-lock"></i> Usuarios y roles</a></li>
            @endif
        </ul>

        {{-- Menú Más (colapsable para lo que casi no se usa) --}}
        <details class="px-2 my-2" @if(request()->routeIs('books.*') || request()->routeIs('loans.*') || request()->routeIs('audit.*')) open @endif>
            <summary style="cursor:pointer;list-style:none;font-size:11px;text-transform:uppercase;letter-spacing:0.04em;color:var(--sidebar-muted);padding:8px 10px;font-weight:600;display:flex;align-items:center;justify-content:space-between">
                <span>MÁS</span>
                <i class="bi bi-chevron-down" style="font-size:10px;"></i>
            </summary>
            <ul class="sidebar-nav" style="padding:0">
                <li><a href="{{ route('books.index') }}" class="{{ request()->routeIs('books.*') || request()->routeIs('loans.*') ? 'active' : '' }}"><i class="bi bi-book"></i> Biblioteca</a></li>
                @if($isAdmin)
                    <li><a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Bitácora del sistema</a></li>
                @endif
            </ul>
        </details>
    @endif

    <ul class="sidebar-nav" style="margin-top:auto;padding-bottom:12px">
        <li>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit" style="background:none;border:none;width:100%;text-align:left;color:var(--sidebar-link);padding:10px 14px;display:flex;align-items:center;gap:11px;font-size:13px;border-radius:10px;cursor:pointer;transition:.15s">
                    <i class="bi bi-box-arrow-right" style="font-size:15px"></i> Cerrar sesión
                </button>
            </form>
        </li>
    </ul>
</aside>
