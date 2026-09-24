<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = ['title', 'description', 'date', 'end_date', 'type', 'created_by'];

    protected $casts = [
        'date' => 'date',
        'end_date' => 'date',
    ];

    public const COLORS = [
        'feriado' => '#e74c3c',
        'examen' => '#9b59b6',
        'reunion' => '#3498db',
        'actividad' => '#2ecc71',
        'civico' => '#e67e22',
        'otro' => '#7f8c8d',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getColorAttribute(): string
    {
        return self::COLORS[$this->type] ?? '#7f8c8d';
    }
}
