<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MercadoPagoWebhookController extends Controller
{
    /**
     * Webhook receptor de notificaciones en tiempo real de Mercado Pago (IPN / Webhooks).
     */
    public function handle(Request $request): JsonResponse
    {
        $type = $request->input('type', $request->input('topic'));
        $dataId = $request->input('data.id', $request->input('id'));

        if ($type === 'payment' && $dataId) {
            $setting = Setting::current();
            $accessToken = $setting->mercadopago_access_token;

            if ($accessToken) {
                try {
                    $mpResponse = Http::withToken($accessToken)
                        ->timeout(10)
                        ->get("https://api.mercadopago.com/v1/payments/{$dataId}");

                    if ($mpResponse->successful()) {
                        $paymentData = $mpResponse->json();
                        $status = $paymentData['status'] ?? null;
                        $externalRef = $paymentData['external_reference'] ?? null;

                        if ($status === 'approved' && $externalRef) {
                            $payment = Payment::find($externalRef);
                            if ($payment && $payment->status !== 'pagado') {
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

                                logger()->info("Pago #{$payment->id} conciliado automáticamente por Mercado Pago Webhook.");
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    logger()->error("Excepción en Webhook Mercado Pago: " . $e->getMessage());
                }
            }
        }

        return response()->json(['status' => 'received'], 200);
    }
}
