<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'logo', 'address', 'phone', 'email',
        'plan', 'status', 'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'date',
    ];

    public const PLANS = ['basico' => 'Básico', 'pro' => 'Profesional', 'institucional' => 'Institucional'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'activo';
    }
}
