<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Comprobante de Pago Electrónico (boleta / factura / nota de crédito)
 * emitido —o pendiente de emisión— ante SUNAT.
 */
class ElectronicInvoice extends Model
{
    use Auditable, BelongsToSchool;

    protected $fillable = [
        'school_id', 'payment_id', 'summary_id', 'tipo_doc',
        'ref_tipo_doc', 'ref_full_number', 'nota_cod_motivo', 'nota_motivo',
        'serie', 'correlativo', 'full_number',
        'fecha_emision', 'moneda', 'client_tipo_doc', 'client_num_doc',
        'client_razon_social', 'client_direccion',
        'op_gravadas', 'igv', 'total', 'items',
        'estado', 'sunat_code', 'sunat_description', 'hash', 'ticket', 'error_message',
        'xml_path', 'cdr_path', 'pdf_path',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'op_gravadas' => 'decimal:2',
        'igv' => 'decimal:2',
        'total' => 'decimal:2',
        'items' => 'array',
    ];

    public const TIPOS = [
        '01' => 'Factura',
        '03' => 'Boleta de venta',
        '07' => 'Nota de crédito',
        '08' => 'Nota de débito',
    ];

    /** Motivos de nota de crédito (catálogo SUNAT 09). */
    public const MOTIVOS_NC = [
        '01' => 'Anulación de la operación',
        '02' => 'Anulación por error en el RUC',
        '03' => 'Corrección por error en la descripción',
        '04' => 'Descuento global',
        '05' => 'Descuento por ítem',
        '06' => 'Devolución total',
        '07' => 'Devolución por ítem',
        '10' => 'Otros conceptos',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function summary(): BelongsTo
    {
        return $this->belongsTo(ElectronicSummary::class, 'summary_id');
    }

    public function isNota(): bool
    {
        return in_array($this->tipo_doc, ['07', '08'], true);
    }

    public function tipoLabel(): string
    {
        return self::TIPOS[$this->tipo_doc] ?? $this->tipo_doc;
    }

    /** Clase de badge del sistema según el estado. */
    public function badgeClass(): string
    {
        return match ($this->estado) {
            'aceptado' => 'badge-pagado',
            'pendiente', 'enviando' => 'badge-pendiente',
            'observado' => 'badge-justificado',
            'anulado' => 'badge-anulado',
            default => 'badge-vencido', // rechazado / error
        };
    }

    public function auditDescription(): string
    {
        return $this->full_number.' · '.$this->tipoLabel();
    }

    /**
     * Calcula el siguiente correlativo para una serie del colegio activo.
     */
    public static function nextCorrelativo(?int $schoolId, string $serie): int
    {
        return (int) static::withoutGlobalScopes()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->where('serie', $serie)
            ->max('correlativo') + 1;
    }
}
