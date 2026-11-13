<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'cover_image',
        'is_public',
    ];

    /**
     * A Playlist belongs to a User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A Playlist has many Songs (through pivot).
     */
    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'playlist_song')->withTimestamps();
    }
}

