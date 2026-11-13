<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'verification_code',
        'verification_code_expires_at',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verification_code_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * User has many Playlists (hasMany).
     * Bir ulanyjynyň köp saznamasy bolup biler.
     */
    public function playlists(): HasMany
    {
        return $this->hasMany(Playlist::class, 'user_id');
    }

    /**
     * User has many public Playlists (hasMany + where).
     * Ulanyjynyň açyk saznamalary.
     */
    public function publicPlaylists(): HasMany
    {
        return $this->hasMany(Playlist::class, 'user_id')->where('is_public', true);
    }

    /**
     * User has many uploaded Songs (hasMany).
     * Ulanyjy köp aýdym ýükläp biler.
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class, 'user_id');
    }
}
