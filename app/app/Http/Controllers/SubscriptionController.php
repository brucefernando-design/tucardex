<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Muestra la pantalla de suscripción / bloqueo cuando el trial de 30 días ha expirado.
     */
    public function expired(Request $request): View|RedirectResponse
    {
        $school = auth()->user()?->school;

        // Si no pertenece a colegio o la prueba sigue vigente / activa, regresar al dashboard
        if (! $school || ! $school->isTrialExpired()) {
            return redirect()->route('dashboard');
        }

        $subscription = $school->calculateMonthlySubscription();
        $isSchoolAdmin = auth()->user()->hasRole('admin');

        $whatsappNumber = config('plans.sales_whatsapp', '5218671234567');
        $waMessage = "Hola TuKardex, soy " . auth()->user()->name . " del colegio " . $school->name . " (Matrícula: " . $subscription['active_students'] . " alumnos). Nuestro periodo de prueba ha finalizado y deseo activar la suscripción en Plan " . $subscription['plan_name'] . ".";
        $whatsappUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', $whatsappNumber) . "?text=" . urlencode($waMessage);

        return view('subscription.expired', compact('school', 'subscription', 'isSchoolAdmin', 'whatsappUrl'));
    }
}
