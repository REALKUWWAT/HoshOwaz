<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bio',
        'image',
        'country',
    ];

    /**
     * Artist has many Songs (hasMany).
     * Bir sazanda köp aýdyma eýedir.
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'artist_id');
    }

    /**
     * Artist-iň iň soňky aýdymlaryny almak (hasMany + scope).
     */
    public function latestSongs(): HasMany
    {
        return $this->hasMany(Song::class, 'artist_id')->latest()->limit(5);
    }

    /**
     * Artist-iň öne çykarylan aýdymlaryny almak (hasMany + where).
     */
    public function featuredSongs(): HasMany
    {
        return $this->hasMany(Song::class, 'artist_id')->where('is_featured', true);
    }
}
