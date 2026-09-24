<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use Auditable, BelongsToSchool, HasFactory;

    protected $fillable = [
        'student_id', 'invoice_number', 'concept', 'amount', 'period',
        'due_date', 'paid_date', 'method', 'status', 'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function electronicInvoices(): HasMany
    {
        return $this->hasMany(ElectronicInvoice::class);
    }

    /** Comprobante electrónico vigente (no rechazado/anulado) del pago, si existe. */
    public function comprobante(): ?ElectronicInvoice
    {
        return $this->electronicInvoices
            ->whereNotIn('estado', ['rechazado', 'error', 'anulado'])
            ->first();
    }
}
