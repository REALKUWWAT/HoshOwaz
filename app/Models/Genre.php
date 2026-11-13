<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
    ];

    /**
     * Genre has many Songs (hasMany).
     * Bir žanrda köp aýdym bolýar.
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'genre_id');
    }

    /**
     * Genre-de öne çykarylan aýdymlar (hasMany + where).
     */
    public function featuredSongs(): HasMany
    {
        return $this->hasMany(Song::class, 'genre_id')->where('is_featured', true);
    }

    /**
     * Genre-de iň köp diňlenen aýdymlar (hasMany + orderBy).
     */
    public function popularSongs(): HasMany
    {
        return $this->hasMany(Song::class, 'genre_id')->orderByDesc('plays_count');
    }
}
