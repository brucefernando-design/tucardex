<?php

namespace App\Http\Controllers;

use App\Models\ElectronicBillingSetting;
use App\Models\ElectronicInvoice;
use App\Models\Payment;
use App\Services\Facturacion\FacturapiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ElectronicBillingController extends Controller
{
    public function __construct(
        protected FacturapiService $facturapi,
        protected \App\Services\Facturacion\FacturamaService $facturama
    ) {}

    protected function getActiveBillingDriver()
    {
        $settings = ElectronicBillingSetting::current();
        if ($settings->pac_driver === 'facturapi') {
            return $this->facturapi;
        }
        return $this->facturama;
    }

    public function index(Request $request): View
    {
        $settings = ElectronicBillingSetting::current();

        $query = ElectronicInvoice::latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_number', 'like', "%{$search}%")
                    ->orWhere('client_razon_social', 'like', "%{$search}%")
                    ->orWhere('client_num_doc', 'like', "%{$search}%")
                    ->orWhere('hash', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_doc', $request->input('tipo'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $invoices = $query->paginate(15)->withQueryString();

        $summary = [
            'aceptados'  => ElectronicInvoice::where('estado', 'aceptado')->count(),
            'pendientes' => ElectronicInvoice::where('estado', 'pendiente')->count(),
            'rechazados' => ElectronicInvoice::whereIn('estado', ['rechazado', 'error'])->count(),
            'total'      => ElectronicInvoice::where('estado', 'aceptado')->sum('total'),
        ];

        return view('facturacion.index', compact('settings', 'invoices', 'summary'));
    }

    public function configuracion(): View
    {
        $settings = ElectronicBillingSetting::current();
        return view('facturacion.configuracion', compact('settings'));
    }

    public function guardar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'auto_emit' => ['nullable', 'boolean'],
            'pac_driver' => ['required', 'in:simulado,facturama,facturapi,finkok'],
            'environment' => ['required', 'in:beta,produccion'],
            'rfc' => ['nullable', 'string', 'min:12', 'max:255'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'regimen_fiscal' => ['nullable', 'string', 'max:10'],
            'codigo_postal' => ['nullable', 'string', 'max:5'],
            'direccion_fiscal' => ['nullable', 'string', 'max:255'],
            'pac_api_key' => ['nullable', 'string', 'max:255'],
            'client_id' => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:255'],
            'clave_prod_serv' => ['nullable', 'string', 'max:10'],
            'clave_unidad' => ['nullable', 'string', 'max:10'],
            'objeto_imp' => ['nullable', 'string', 'max:5'],
            'serie_factura' => ['required', 'string', 'max:10'],
            'serie_boleta' => ['required', 'string', 'max:10'],
            'serie_nc_factura' => ['nullable', 'string', 'max:10'],
            'serie_nc_boleta' => ['nullable', 'string', 'max:10'],
            'igv_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'moneda' => ['required', 'string', 'max:3'],
        ]);

        $data['enabled'] = $request->boolean('enabled');
        $data['auto_emit'] = $request->boolean('auto_emit');
        $data['igv_percent'] = 0.00; // Siempre 0 en México para educación exenta

        $settings = ElectronicBillingSetting::current();
        $settings->fill($data)->save();

        return redirect()->route('facturacion.configuracion')
            ->with('success', 'Configuración de Facturación SAT (CFDI 4.0) guardada exitosamente.');
    }

    public function probar(): RedirectResponse
    {
        $res = $this->getActiveBillingDriver()->testConnection();

        if ($res['ok']) {
            return redirect()->route('facturacion.configuracion')
                ->with('success', '✅ ' . $res['message']);
        }

        return redirect()->route('facturacion.configuracion')
            ->with('error', '❌ ' . $res['message']);
    }

    public function emitirDesdePago(Payment $payment): RedirectResponse
    {
        $invoice = $this->getActiveBillingDriver()->emitForPayment($payment);

        if ($invoice->estado === 'aceptado') {
            return back()->with('success', "CFDI 4.0 emitido exitosamente. Folio Fiscal: {$invoice->hash}");
        }

        return back()->with('error', "Error al timbrar factura ante el SAT: {$invoice->error_message}");
    }

    public function show(ElectronicInvoice $invoice): View
    {
        return view('facturacion.show', compact('invoice'));
    }

    public function pdf(ElectronicInvoice $invoice)
    {
        $facturapiId = $invoice->sunat_code;
        if (!$facturapiId) {
            return back()->with('error', 'El identificador de Facturapi no está disponible.');
        }

        $content = $this->getActiveBillingDriver()->downloadFile($facturapiId, 'pdf');
        if ($content) {
            return response($content, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . ($invoice->full_number ?: 'CFDI') . '.pdf"',
            ]);
        }

        return back()->with('error', 'No fue posible obtener el PDF desde el servicio de facturación.');
    }

    public function descargarXml(ElectronicInvoice $invoice)
    {
        $facturapiId = $invoice->sunat_code;
        if (!$facturapiId) {
            return back()->with('error', 'El identificador de Facturapi no está disponible.');
        }

        $content = $this->getActiveBillingDriver()->downloadFile($facturapiId, 'xml');
        if ($content) {
            return response($content, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="' . ($invoice->full_number ?: 'CFDI') . '.xml"',
            ]);
        }

        return back()->with('error', 'No fue posible obtener el XML desde el servicio de facturación.');
    }

    public function anular(ElectronicInvoice $invoice): RedirectResponse
    {
        $driver = $this->getActiveBillingDriver();
        if (method_exists($driver, 'cancel')) {
            $success = $driver->cancel($invoice);
            if ($success) {
                return back()->with('success', 'Factura cancelada ante el SAT correctamente.');
            }
        }

        return back()->with('error', 'No fue posible cancelar la factura ante el SAT.');
    }

    public function reenviar(ElectronicInvoice $invoice): RedirectResponse
    {
        if ($invoice->payment) {
            $newInvoice = $this->getActiveBillingDriver()->emitForPayment($invoice->payment);
            if ($newInvoice->estado === 'aceptado') {
                $invoice->delete();
                return back()->with('success', "CFDI 4.0 re-emitido y timbrado exitosamente.");
            }
            return back()->with('error', "Error al timbrar: {$newInvoice->error_message}");
        }

        return back()->with('error', 'Esta factura no tiene un pago asociado para reintentar.');
    }

    public function resumenes(): View
    {
        return view('facturacion.resumenes');
    }
}
