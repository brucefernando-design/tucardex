<?php

namespace App\Models\Concerns;

use App\Models\School;
use App\Services\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aísla los datos por colegio (tenant):
 *  - Al crear un registro asigna automáticamente el colegio activo.
 *  - Al consultar filtra automáticamente por el colegio activo.
 * Cuando no hay colegio activo (super-admin, seeding, páginas públicas)
 * no se aplica ningún filtro.
 */
trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        $tenancy = app(Tenancy::class);

        static::creating(function ($model) use ($tenancy) {
            if ($tenancy->check() && empty($model->school_id)) {
                $model->school_id = $tenancy->id();
            }
        });

        static::addGlobalScope('school', function (Builder $builder) use ($tenancy) {
            if ($tenancy->check()) {
                $builder->where($builder->getModel()->getTable().'.school_id', $tenancy->id());
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
