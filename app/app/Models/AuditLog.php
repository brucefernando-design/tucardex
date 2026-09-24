<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'user_id', 'action', 'auditable_type', 'auditable_id', 'description',
    ];

    public const LABELS = [
        'Student' => 'Estudiante',
        'Teacher' => 'Docente',
        'Course' => 'Curso',
        'Subject' => 'Materia',
        'Payment' => 'Pago',
        'Grade' => 'Calificación',
        'User' => 'Usuario',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEntityLabelAttribute(): string
    {
        return self::LABELS[$this->auditable_type] ?? $this->auditable_type;
    }
}
