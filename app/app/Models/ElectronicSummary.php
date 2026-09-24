<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Resumen Diario de boletas (RC) enviado a SUNAT. Agrupa las boletas de una
 * fecha y se procesa de forma asíncrona mediante un ticket.
 */
class ElectronicSummary extends Model
{
    use Auditable, BelongsToSchool;

    protected $fillable = [
        'school_id', 'fecha_generacion', 'fecha_resumen', 'correlativo', 'identifier',
        'estado', 'ticket', 'sunat_code', 'sunat_description', 'error_message',
        'total_docs', 'total', 'xml_path', 'cdr_path',
    ];

    protected $casts = [
        'fecha_generacion' => 'date',
        'fecha_resumen' => 'date',
        'total' => 'decimal:2',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(ElectronicInvoice::class, 'summary_id');
    }

    public function badgeClass(): string
    {
        return match ($this->estado) {
            'aceptado' => 'badge-pagado',
            'pendiente', 'enviando', 'ticket' => 'badge-pendiente',
            'observado' => 'badge-justificado',
            default => 'badge-vencido',
        };
    }

    public function auditDescription(): string
    {
        return $this->identifier;
    }

    /** Siguiente correlativo de resumen para una fecha del colegio activo. */
    public static function nextCorrelativo(?int $schoolId, $fecha): int
    {
        return (int) static::withoutGlobalScopes()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereDate('fecha_generacion', $fecha)
            ->max('correlativo') + 1;
    }
}
