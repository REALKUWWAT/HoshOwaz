<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Song extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'artist_id',
        'genre_id',
        'user_id',
        'duration',
        'audio_path',
        'cover_image',
        'lyrics',
        'plays_count',
        'is_featured',
    ];

    /**
     * A Song belongs to an Artist.
     */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    /**
     * A Song belongs to a Genre.
     */
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    /**
     * A Song belongs to an uploader User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A Song belongs to many Playlists.
     */
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_song')->withTimestamps();
    }
}

