<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\Artist;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class SongController extends Controller
{
    /**
     * Display a listing of songs with search and filter.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $genreId = $request->input('genre_id');

        $query = Song::with(['artist', 'genre'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('artist', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($genreId) {
            $query->where('genre_id', $genreId);
        }

        $songs = $query->paginate(12)->withQueryString();
        $genres = Genre::all();
        $selectedGenre = $genreId ? Genre::find($genreId) : null;

        return view('songs.index', compact('songs', 'genres', 'selectedGenre', 'search'));
    }

    /**
     * Show form for creating a new song.
     */
    public function create(): View
    {
        $artists = Artist::orderBy('name')->get();
        $genres = Genre::orderBy('name')->get();

        return view('songs.create', compact('artists', 'genres'));
    }

    /**
     * Store a newly created song in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'artist_id' => 'required|exists:artists,id',
            'genre_id' => 'nullable|exists:genres,id',
            'duration' => 'nullable|string|max:10',
            'audio_path' => 'nullable|string',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,aac|max:25600',
            'cover_image' => 'nullable|string',
            'cover_file' => 'nullable|image|max:5120',
            'lyrics' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
        ]);

        $title = $request->input('title');
        $artistId = $request->input('artist_id');
        $genreId = $request->input('genre_id');
        $duration = $request->input('duration') ?: '3:30';
        $lyrics = $request->input('lyrics');
        $isFeatured = (bool) $request->input('is_featured', false);

        // Handle Audio
        $audioPath = $request->input('audio_path');
        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('audio', 'public');
            $audioPath = asset('storage/' . $path);
        }
        if (!$audioPath) {
            $audioPath = asset('audio/owadan_diyar.wav');
        }

        // Handle Cover Image
        $coverImage = $request->input('cover_image');
        if ($request->hasFile('cover_file')) {
            $path = $request->file('cover_file')->store('covers', 'public');
            $coverImage = asset('storage/' . $path);
        }
        if (!$coverImage) {
            $coverImage = asset('images/covers/default.svg');
        }

        $song = Song::create([
            'title' => $title,
            'artist_id' => $artistId,
            'genre_id' => $genreId,
            'user_id' => Auth::id(),
            'duration' => $duration,
            'audio_path' => $audioPath,
            'cover_image' => $coverImage,
            'lyrics' => $lyrics,
            'is_featured' => $isFeatured,
            'plays_count' => 0,
        ]);

        return redirect()->route('songs.show', $song->id)->with('success', __('messages.song_created'));
    }

    /**
     * Display the specified song.
     * hasMany() relýasiýalary arkaly maglumatlar:
     *  - Genre->songs() hasMany: şol žanrdaky meňzeş aýdymlar
     *  - Artist->songs() hasMany: şol sazandanyň beýleki aýdymlary
     *  - User->playlists() hasMany: ulanyjynyň öz saznamalary
     */
    public function show(int $id): View
    {
        $song = Song::with(['artist', 'genre', 'user'])->findOrFail($id);
        $song->increment('plays_count');

        // hasMany() -> Genre->songs() — şol žanrdaky beýleki aýdymlar
        $relatedSongs = $song->genre
            ? $song->genre->songs()->with(['artist', 'genre'])
                ->where('id', '!=', $id)
                ->take(4)
                ->get()
            : collect();

        // hasMany() -> Artist->songs() — şol sazandanyň beýleki aýdymlary
        $artistOtherSongs = $song->artist
            ? $song->artist->songs()->with('genre')
                ->where('id', '!=', $id)
                ->latest()
                ->take(4)
                ->get()
            : collect();

        // hasMany() -> Auth::user()->playlists() — giriş eden ulanyjynyň saznamalary
        $userPlaylists = Auth::check()
            ? Auth::user()->playlists()->latest()->get()
            : collect();

        return view('songs.show', compact('song', 'relatedSongs', 'artistOtherSongs', 'userPlaylists'));
    }

    /**
     * Show form for editing the song.
     */
    public function edit(int $id): View
    {
        $song = Song::findOrFail($id);
        $artists = Artist::orderBy('name')->get();
        $genres = Genre::orderBy('name')->get();

        return view('songs.edit', compact('song', 'artists', 'genres'));
    }

    /**
     * Update the specified song in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $song = Song::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:200',
            'artist_id' => 'required|exists:artists,id',
            'genre_id' => 'nullable|exists:genres,id',
            'duration' => 'nullable|string|max:10',
            'audio_path' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'lyrics' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
        ]);

        $song->title = $request->input('title');
        $song->artist_id = $request->input('artist_id');
        $song->genre_id = $request->input('genre_id');
        $song->duration = $request->input('duration', $song->duration);
        $song->audio_path = $request->input('audio_path', $song->audio_path);
        $song->cover_image = $request->input('cover_image', $song->cover_image);
        $song->lyrics = $request->input('lyrics');
        $song->is_featured = (bool) $request->input('is_featured', false);

        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('audio', 'public');
            $song->audio_path = asset('storage/' . $path);
        }

        if ($request->hasFile('cover_file')) {
            $path = $request->file('cover_file')->store('covers', 'public');
            $song->cover_image = asset('storage/' . $path);
        }

        $song->save();

        return redirect()->route('songs.show', $song->id)->with('success', __('messages.song_updated'));
    }

    /**
     * Remove the specified song from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $song = Song::findOrFail($id);
        $song->delete();

        return redirect()->route('songs.index')->with('success', __('messages.song_deleted'));
    }

    /**
     * JSON payload for player widget.
     */
    public function play(int $id): JsonResponse
    {
        $song = Song::with('artist')->findOrFail($id);
        $song->increment('plays_count');

        return response()->json([
            'id' => $song->id,
            'title' => $song->title,
            'artist' => $song->artist->name,
            'duration' => $song->duration,
            'audio_path' => $song->audio_path,
            'cover_image' => $song->cover_image,
            'plays_count' => $song->plays_count,
        ]);
    }
}

