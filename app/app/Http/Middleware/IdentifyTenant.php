<?php

namespace App\Http\Middleware;

use App\Services\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Establece el colegio activo a partir del usuario autenticado.
 * El super-administrador (sin colegio) no activa ningún tenant.
 */
class IdentifyTenant
{
    public function __construct(protected Tenancy $tenancy)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->school_id && ! $user->isSuperAdmin()) {
            $this->tenancy->set($user->school_id);
            $school = $user->school;

            if ($school) {
                // Bloquear acceso si el colegio está suspendido
                if (! $school->isActive() && ! $request->routeIs('login', 'logout', 'schools.leave_impersonation') && ! session()->has('impersonator_id')) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->withErrors([
                        'email' => 'La cuenta de tu colegio está suspendida. Contacta al administrador de la plataforma.',
                    ]);
                }

                // Bloquear acceso si el periodo de prueba de 30 días ha concluido
                if ($school->isTrialExpired() && ! $request->routeIs('subscription.*', 'logout')) {
                    return redirect()->route('subscription.expired');
                }
            }
        }

        return $next($request);
    }
}
