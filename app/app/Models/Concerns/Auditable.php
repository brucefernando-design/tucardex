<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;

/**
 * Registra en la bitácora de auditoría las operaciones de creación,
 * actualización y eliminación de un modelo. Solo registra cuando hay
 * un usuario autenticado (evita ruido durante el seeding).
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn ($model) => $model->writeAudit('creó'));
        static::updated(fn ($model) => $model->writeAudit('actualizó'));
        static::deleted(fn ($model) => $model->writeAudit('eliminó'));
    }

    public function writeAudit(string $action): void
    {
        if (! auth()->check()) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => class_basename($this),
            'auditable_id' => $this->getKey(),
            'description' => $this->auditDescription(),
        ]);
    }

    /**
     * Etiqueta legible del registro auditado (sobreescribible por el modelo).
     */
    public function auditDescription(): string
    {
        foreach (['full_name', 'name', 'title', 'concept', 'invoice_number'] as $attr) {
            if (! empty($this->{$attr})) {
                return (string) $this->{$attr};
            }
        }

        return class_basename($this).' #'.$this->getKey();
    }
}
