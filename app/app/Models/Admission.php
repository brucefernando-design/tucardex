<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admission extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id', 'folio', 'first_name', 'last_name', 'curp', 'birth_date',
        'gender', 'course_id', 'grade_level', 'previous_school',
        'guardian_name', 'guardian_relationship', 'guardian_phone', 'guardian_email',
        'address', 'birth_certificate_path', 'curp_path', 'address_proof_path',
        'previous_grades_path', 'medical_notes', 'status', 'internal_notes', 'student_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public static function generateFolio(int $schoolId): string
    {
        $year = date('Y');
        $count = static::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return 'ADM-' . $year . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'pendiente' => '<span class="badge bg-warning text-dark border"><i class="bi bi-clock me-1"></i> Pendiente</span>',
            'en_revision' => '<span class="badge bg-info text-dark border"><i class="bi bi-search me-1"></i> En Revisión</span>',
            'aceptada' => '<span class="badge bg-success border"><i class="bi bi-check-circle me-1"></i> Aceptada</span>',
            'rechazada' => '<span class="badge bg-danger border"><i class="bi bi-x-circle me-1"></i> Rechazada</span>',
            'matriculada' => '<span class="badge bg-primary border"><i class="bi bi-mortarboard me-1"></i> Matriculada</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }
}
