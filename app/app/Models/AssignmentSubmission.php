<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmission extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'school_id', 'assignment_id', 'student_id', 'status',
        'file', 'comment', 'score', 'feedback', 'submitted_at', 'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file) {
            return null;
        }

        $disk = config('filesystems.default');
        if ($disk === 'r2' || $disk === 's3') {
            try {
                return Storage::disk($disk)->temporaryUrl($this->file, now()->addMinutes(60));
            } catch (\Throwable $e) {
                return Storage::disk($disk)->url($this->file);
            }
        }

        return asset('storage/'.$this->file);
    }
}
