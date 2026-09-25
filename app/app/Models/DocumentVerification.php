<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentVerification extends Model
{
    protected $fillable = [
        'token',
        'school_id',
        'student_id',
        'doc_type',
        'doc_title',
        'folio',
        'student_name',
        'student_code',
        'student_curp',
        'course_name',
        'level',
        'academic_year',
        'school_name',
        'school_cct',
        'director_name',
        'extra_data',
        'issued_at',
    ];

    protected $casts = [
        'extra_data' => 'array',
        'issued_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Emite o recupera un registro de verificación para un documento de estudiante
     */
    public static function issueForStudent(
        Student $student,
        Setting $setting,
        string $docType,
        string $docTitle,
        ?string $folio = null,
        array $extraData = []
    ): self {
        $academicYear = $setting->academic_year ?: (optional($student->course)->academic_year ?: date('Y'));
        $courseName = optional($student->course)->full_name ?: trim((optional($student->course)->name ?? '') . ' ' . (optional($student->course)->section ?? ''));

        try {
            $existing = static::where('school_id', $student->school_id)
                ->where('student_id', $student->id)
                ->where('doc_type', $docType)
                ->where('academic_year', $academicYear)
                ->first();

            if ($existing) {
                $existing->update([
                    'doc_title' => $docTitle,
                    'folio' => $folio ?: $existing->folio,
                    'student_name' => $student->full_name,
                    'student_code' => $student->code,
                    'student_curp' => $student->curp ?: $student->dni,
                    'course_name' => $courseName ?: 'Sin grupo',
                    'level' => optional($student->course)->level,
                    'school_name' => $setting->school_name ?: 'Institución Educativa',
                    'school_cct' => $setting->cct,
                    'director_name' => $setting->director,
                    'extra_data' => array_merge($existing->extra_data ?? [], $extraData),
                    'issued_at' => now(),
                ]);
                return $existing;
            }

            return static::create([
                'token' => bin2hex(random_bytes(12)), // 24 caracteres aleatorios únicos
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'doc_type' => $docType,
                'doc_title' => $docTitle,
                'folio' => $folio,
                'student_name' => $student->full_name,
                'student_code' => $student->code,
                'student_curp' => $student->curp ?: $student->dni,
                'course_name' => $courseName ?: 'Sin grupo',
                'level' => optional($student->course)->level,
                'academic_year' => $academicYear,
                'school_name' => $setting->school_name ?: 'Institución Educativa',
                'school_cct' => $setting->cct,
                'director_name' => $setting->director,
                'extra_data' => $extraData,
                'issued_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fallback en memoria si la BD tiene algún inconveniente
            $fallback = new static([
                'token' => bin2hex(random_bytes(12)),
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'doc_type' => $docType,
                'doc_title' => $docTitle,
                'folio' => $folio,
                'student_name' => $student->full_name,
                'student_code' => $student->code,
                'student_curp' => $student->curp ?: $student->dni,
                'course_name' => $courseName ?: 'Sin grupo',
                'level' => optional($student->course)->level,
                'academic_year' => $academicYear,
                'school_name' => $setting->school_name ?: 'Institución Educativa',
                'school_cct' => $setting->cct,
                'director_name' => $setting->director,
                'extra_data' => $extraData,
                'issued_at' => now(),
            ]);
            return $fallback;
        }
    }

    /**
     * URL pública de verificación
     */
    public function getVerificationUrlAttribute(): string
    {
        return route('documentos.verificar', ['token' => $this->token]);
    }
}
