<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use BelongsToSchool, HasFactory;

    protected $table = 'app_notifications';

    protected $fillable = [
        'school_id', 'user_id', 'type', 'title', 'body', 'url', 'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public const ICONS = [
        'grade' => 'bi-clipboard-data',
        'payment' => 'bi-cash-coin',
        'announcement' => 'bi-megaphone',
        'task' => 'bi-journal-text',
        'info' => 'bi-info-circle',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function getIconAttribute(): string
    {
        return self::ICONS[$this->type] ?? 'bi-bell';
    }

    /**
     * Crea una notificación para un usuario. Si $userId es nulo no hace nada.
     */
    public static function notify(?int $userId, string $title, string $type = 'info', ?string $body = null, ?string $url = null): void
    {
        if (! $userId) {
            return;
        }

        static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ]);
    }

    public static function unreadFor(int $userId): int
    {
        return static::where('user_id', $userId)->whereNull('read_at')->count();
    }
}
