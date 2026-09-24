<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = [
        'title', 'author', 'isbn', 'category', 'editorial',
        'location', 'quantity', 'available',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
