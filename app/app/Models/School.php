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
        'plan', 'price_per_student', 'minimum_monthly_fee', 'is_public_school',
        'canvas_enabled', 'api_enabled', 'subscription_status',
        'billing_renews_at', 'features_override', 'status', 'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'date',
        'billing_renews_at' => 'date',
        'price_per_student' => 'decimal:2',
        'minimum_monthly_fee' => 'decimal:2',
        'is_public_school' => 'boolean',
        'canvas_enabled' => 'boolean',
        'api_enabled' => 'boolean',
        'features_override' => 'array',
    ];

    public const PLANS = [
        'basico' => 'Básico',
        'pro' => 'Profesional',
        'institucional' => 'Integral',
    ];

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
        return $this->status === 'activo' && $this->subscription_status !== 'suspended' && $this->subscription_status !== 'cancelled';
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
        if ($this->isOnTrial()) {
            return 'pro';
        }
        return in_array($this->plan, ['basico', 'pro', 'institucional'], true) ? $this->plan : 'basico';
    }

    public function planConfig(): array
    {
        $plans = config('plans.plans', []);
        return $plans[$this->effectivePlan()] ?? $plans['basico'] ?? [];
    }

    public function planName(): string
    {
        return $this->planConfig()['name'] ?? 'Básico';
    }

    /**
     * Consulta si la institución tiene habilitada una funcionalidad específica.
     */
    public function hasFeature(string $feature): bool
    {
        // 1. Overrides manuales por institución (ej. convenios especiales)
        if (is_array($this->features_override) && array_key_exists($feature, $this->features_override)) {
            return (bool) $this->features_override[$feature];
        }

        // 2. Flags booleanos directos en el modelo
        if ($feature === 'canvas' && $this->canvas_enabled) {
            return true;
        }
        if ($feature === 'api' && $this->api_enabled) {
            return true;
        }

        // 3. Matriz del plan activo
        $config = $this->planConfig();
        return (bool) ($config['features'][$feature] ?? false);
    }

    /**
     * Conteo de alumnos activos para cálculo de suscripción.
     */
    public function activeStudentsCount(): int
    {
        return $this->students()->where('status', 'activo')->count();
    }

    /**
     * Precio por alumno efectivo en MXN.
     */
    public function effectivePricePerStudent(): float
    {
        if ((float) $this->price_per_student > 0) {
            return (float) $this->price_per_student;
        }

        $config = $this->planConfig();
        if ($this->is_public_school && isset($config['public_school_price'])) {
            return (float) $config['public_school_price'];
        }

        return (float) ($config['price_per_student'] ?? 15.00);
    }

    /**
     * Mínimo mensual garantizado en MXN.
     */
    public function effectiveMinimumMonthlyFee(): float
    {
        if ((float) $this->minimum_monthly_fee > 0) {
            return (float) $this->minimum_monthly_fee;
        }

        if ($this->is_public_school) {
            return 2000.00;
        }

        return 0.00;
    }

    /**
     * Cálculo de la suscripción mensual:
     * max(alumnos_activos * precio_alumno, minimo_mensual)
     */
    public function calculateMonthlySubscription(): array
    {
        $studentsCount = $this->activeStudentsCount();
        $unitPrice = $this->effectivePricePerStudent();
        $subtotal = $studentsCount * $unitPrice;
        $minimumFee = $this->effectiveMinimumMonthlyFee();
        $finalAmount = max($subtotal, $minimumFee);

        return [
            'active_students' => $studentsCount,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'minimum_fee' => $minimumFee,
            'applied_minimum' => $subtotal < $minimumFee,
            'total' => $finalAmount,
            'currency' => 'MXN',
            'plan_name' => $this->planName(),
            'status' => $this->subscription_status ?? 'active',
        ];
    }

    public function maxStudents(): int
    {
        // En el nuevo modelo no se restringe por cupo rígido, sino por volumen facturable
        return match ($this->effectivePlan()) {
            'basico' => 10000,
            default => 50000,
        };
    }

    public function canAddStudent(): bool
    {
        return $this->isActive();
    }

    public function canAccessPayments(): bool
    {
        return $this->hasFeature('billing');
    }

    public function canGenerateTuitionBatch(): bool
    {
        return $this->hasFeature('billing');
    }

    public function canAddAdminUser(): bool
    {
        if ($this->effectivePlan() === 'basico') {
            $adminRole = Role::where('slug', 'admin')->first();
            $adminCount = $this->users()->where('role_id', optional($adminRole)->id)->count();
            return $adminCount < 2;
        }
        return true;
    }
}
