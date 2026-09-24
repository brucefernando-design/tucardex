<?php

namespace App\Services;

/**
 * Mantiene el colegio (tenant) activo durante el ciclo de la petición.
 * Se registra como singleton en el contenedor.
 */
class Tenancy
{
    protected ?int $schoolId = null;

    public function set(?int $schoolId): void
    {
        $this->schoolId = $schoolId;
    }

    public function id(): ?int
    {
        return $this->schoolId;
    }

    public function check(): bool
    {
        return $this->schoolId !== null;
    }

    public function forget(): void
    {
        $this->schoolId = null;
    }
}
