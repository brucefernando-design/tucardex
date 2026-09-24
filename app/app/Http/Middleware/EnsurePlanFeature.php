<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanFeature
{
    /**
     * Valida que el colegio del usuario tenga habilitada la función según su plan y estado de trial.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        // Superadmin tiene acceso irrestricto
        if ($user?->isSuperAdmin()) {
            return $next($request);
        }

        $school = $user?->school;
        if (! $school) {
            return $next($request);
        }

        $allowed = match ($feature) {
            'payments' => $school->canAccessPayments(),
            'generate_tuition' => $school->canGenerateTuitionBatch(),
            default => true,
        };

        if (! $allowed) {
            $planLabel = School::PLANS[$school->plan] ?? ucfirst($school->plan);
            $msg = "Tu plan actual ({$planLabel}) no incluye el módulo de pagos avanzados ni generación masiva de colegiaturas. Pasa a Plan Profesional para habilitarlo.";

            if ($request->expectsJson()) {
                return response()->json(['error' => $msg], 403);
            }

            return redirect()->route('dashboard')->with('error', $msg);
        }

        return $next($request);
    }
}
