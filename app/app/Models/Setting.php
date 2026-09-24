<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use App\Services\Tenancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'school_name', 'cct', 'rvoe', 'logo', 'academic_year', 'active_period', 'currency',
        'address', 'phone', 'director', 'cedula_profesional', 'tuition_amount',
        'spei_enabled', 'spei_bank', 'spei_clabe', 'spei_beneficiary', 'spei_instructions',
        'mercadopago_enabled', 'mercadopago_public_key', 'mercadopago_access_token',
        'stripe_enabled', 'stripe_public_key', 'stripe_secret_key',
    ];

    protected $casts = [
        'spei_enabled'               => 'boolean',
        'mercadopago_enabled'        => 'boolean',
        'stripe_enabled'             => 'boolean',
        // Credenciales sensibles cifradas con AES-256-CBC (APP_KEY)
        'spei_clabe'                 => 'encrypted',
        'mercadopago_access_token'   => 'encrypted',
        'stripe_secret_key'          => 'encrypted',
    ];

    public static function current(): self
    {
        $tenancy = app(Tenancy::class);

        if (! $tenancy->check()) {
            return new static([
                'school_name' => 'TuCardex',
                'currency' => '$',
                'academic_year' => date('Y') . ' - ' . (date('Y') + 1),
                'active_period' => '1er Trimestre',
                'spei_enabled' => true,
            ]);
        }

        return Cache::rememberForever('app_settings_'.$tenancy->id(), function () {
            return static::first() ?? static::create([
                'school_name' => 'TuCardex',
                'currency' => '$',
                'academic_year' => date('Y') . ' - ' . (date('Y') + 1),
                'active_period' => '1er Trimestre',
                'spei_enabled' => true,
            ]);
        });
    }

    protected static function booted(): void
    {
        static::saved(function ($setting) {
            Cache::forget('app_settings_'.$setting->school_id);
        });
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/'.$this->logo) : null;
    }

    public function getLogoBase64Attribute(): ?string
    {
        if (! $this->logo || ! Storage::disk('public')->exists($this->logo)) {
            return null;
        }

        $ext = strtolower(pathinfo($this->logo, PATHINFO_EXTENSION));
        $mime = $ext === 'svg' ? 'image/svg+xml' : ($ext === 'png' ? 'image/png' : ($ext === 'webp' ? 'image/webp' : 'image/jpeg'));
        $data = base64_encode(Storage::disk('public')->get($this->logo));

        return "data:{$mime};base64,{$data}";
    }
}
