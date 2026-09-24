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

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function trialDaysRemaining(): int
    {
        if (! $this->isOnTrial()) {
            return 0;
        }
        return (int) ceil(now()->diffInDays($this->trial_ends_at, false));
    }

    public function effectivePlan(): string
    {
        // Durante los 30 días de prueba, el colegio cuenta con las características del plan profesional
        if ($this->isOnTrial()) {
            return 'pro';
        }
        return $this->plan ?? 'basico';
    }

    public function maxStudents(): int
    {
        return match ($this->effectivePlan()) {
            'basico' => 100,
            default => 2000,
        };
    }

    public function canAddStudent(): bool
    {
        $count = $this->students()->where('status', 'activo')->count();
        return $count < $this->maxStudents();
    }

    public function canAccessPayments(): bool
    {
        return in_array($this->effectivePlan(), ['pro', 'institucional']);
    }

    public function canGenerateTuitionBatch(): bool
    {
        return in_array($this->effectivePlan(), ['pro', 'institucional']);
    }

    public function canAddAdminUser(): bool
    {
        if ($this->effectivePlan() === 'basico') {
            $adminRole = Role::where('slug', 'admin')->first();
            $adminCount = $this->users()->where('role_id', optional($adminRole)->id)->count();
            return $adminCount < 1;
        }
        return true;
    }

}
