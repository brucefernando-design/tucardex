<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'book_id', 'student_id', 'borrower_name',
        'loan_date', 'due_date', 'return_date', 'status',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getBorrowerLabelAttribute(): string
    {
        return $this->student?->full_name ?? $this->borrower_name ?? '—';
    }
}
