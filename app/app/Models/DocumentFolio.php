<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentFolio extends Model
{
    protected $fillable = ['school_id', 'doc_type', 'year', 'current_number'];

    /**
     * Genera el siguiente folio consecutivo por escuela y año (ej: OFC-2026-0001)
     */
    public static function nextFolio(int $schoolId, string $docType = 'OFC', ?int $year = null): string
    {
        $year = $year ?: (int) date('Y');

        return DB::transaction(function () use ($schoolId, $docType, $year) {
            $record = static::firstOrCreate(
                ['school_id' => $schoolId, 'doc_type' => $docType, 'year' => $year],
                ['current_number' => 0]
            );
            $record->increment('current_number');

            return sprintf("%s-%d-%04d", strtoupper($docType), $year, $record->current_number);
        });
    }
}
