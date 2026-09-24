<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\Tenancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MercadoPagoWebhookController extends Controller
{
    /**
     * Webhook receptor de notificaciones en tiempo real de Mercado Pago (IPN / Webhooks).
     * Blindado contra errores de payload, excepciones no controladas y seguro en entornos multi-tenant.
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', $request->input('topic'));
            $dataId = $request->input('data.id', $request->input('id'));

            if ($type !== 'payment' || ! $dataId) {
                return response()->json(['status' => 'ignored'], 200);
            }

            // Identificar el colegio: si viene en query o si buscamos entre colegios activos con MP
            $schoolId = $request->query('school');
            $candidateSettings = $schoolId
                ? Setting::withoutGlobalScopes()->where('school_id', $schoolId)->whereNotNull('mercadopago_access_token')->get()
                : Setting::withoutGlobalScopes()->where('mercadopago_enabled', true)->whereNotNull('mercadopago_access_token')->get();

            if ($candidateSettings->isEmpty()) {
                logger()->warning("Webhook Mercado Pago recibido para ID #{$dataId} pero no hay credenciales configuradas.");
                return response()->json(['status' => 'no_merchant_configured'], 200);
            }

            foreach ($candidateSettings as $setting) {
                $accessToken = $setting->mercadopago_access_token;
                if (! $accessToken) {
                    continue;
                }

                $mpResponse = Http::withToken($accessToken)
                    ->timeout(10)
                    ->get("https://api.mercadopago.com/v1/payments/{$dataId}");

                if (! $mpResponse->successful()) {
                    continue;
                }

                $paymentData = $mpResponse->json();
                $status = $paymentData['status'] ?? null;
                $externalRef = $paymentData['external_reference'] ?? null;

                if (! $externalRef) {
                    continue;
                }

                // Buscar el cobro por token seguro o por ID numérico previo
                $payment = Payment::withoutGlobalScopes()
                    ->where('token', $externalRef)
                    ->orWhere(function ($query) use ($externalRef) {
                        if (is_numeric($externalRef)) {
                            $query->where('id', (int) $externalRef);
                        }
                    })
                    ->first();

                if ($payment) {
                    // Establecer el contexto del colegio para que los eventos y logs queden aislados
                    app(Tenancy::class)->set($payment->school_id);

                    if ($status === 'approved' && $payment->status !== 'pagado') {
                        $methodType = $paymentData['payment_type_id'] ?? 'tarjeta';
                        $method = match ($methodType) {
                            'bank_transfer' => 'transferencia',
                            'ticket' => 'efectivo',
                            default => 'tarjeta',
                        };

                        $payment->update([
                            'status' => 'pagado',
                            'paid_date' => now(),
                            'method' => $method,
                            'remarks' => "Acreditado automáticamente vía Webhook Mercado Pago #{$dataId}",
                        ]);

                        logger()->info("Pago #{$payment->id} conciliado automáticamente vía Webhook Mercado Pago para colegio #{$payment->school_id}.");
                    }

                    return response()->json(['status' => 'success', 'payment_id' => $payment->id], 200);
                }
            }
        } catch (\Throwable $e) {
            logger()->error("Excepción en Webhook Mercado Pago: " . $e->getMessage());
        }

        return response()->json(['status' => 'received'], 200);
    }
}
