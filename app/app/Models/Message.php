<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = ['sender_id', 'recipient_id', 'subject', 'body', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public static function unreadCountFor(int $userId): int
    {
        return static::where('recipient_id', $userId)->whereNull('read_at')->count();
    }
}
