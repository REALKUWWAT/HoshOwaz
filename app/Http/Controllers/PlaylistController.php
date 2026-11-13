<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Song;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PlaylistController extends Controller
{
    /**
     * Display a listing of public and user playlists.
     * hasMany() -> Auth::user()->playlists() — User modelindäki playlists() hasMany relýasiýasy
     */
    public function index(): View
    {
        $publicPlaylists = Playlist::with(['user', 'songs.artist'])
            ->where('is_public', true)
            ->latest()
            ->paginate(12);

        // hasMany() -> Auth kullanıcının saznamalaryny almak
        // User::playlists() — hasMany(Playlist::class, 'user_id') relýasiýasy
        $myPlaylists = Auth::check()
            ? Auth::user()->playlists()->with(['songs.artist'])->latest()->get()
            : collect();

        // hasMany() -> Her ulanyjy playlist döredip biler, sanyny görkezeliň
        $myPlaylistsCount = Auth::check()
            ? Auth::user()->playlists()->count()
            : 0;

        return view('playlists.index', compact('publicPlaylists', 'myPlaylists', 'myPlaylistsCount'));
    }

    /**
     * Show the form for creating a new playlist.
     */
    public function create(): View
    {
        return view('playlists.create');
    }

    /**
     * Store a newly created playlist in storage.
     * hasMany() -> Auth::user()->playlists()->create() — hasMany relýasiýasy arkaly döredýäris
     */
    public function store(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('warning', __('messages.login'));
        }

        $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_public'   => 'nullable|boolean',
            'cover_image' => 'nullable|string',
        ]);

        $name        = $request->input('name');
        $description = $request->input('description');
        $isPublic    = (bool) $request->input('is_public', true);
        $coverImage  = $request->input('cover_image') ?: asset('images/covers/default.svg');

        // hasMany() -> Auth::user()->playlists()->create() — directly through hasMany relation
        // Bu usul user_id-ni özbaşdak doldurmagyň zerurlygyny aradan aýyrýar
        $playlist = Auth::user()->playlists()->create([
            'name'        => $name,
            'description' => $description,
            'cover_image' => $coverImage,
            'is_public'   => $isPublic,
        ]);

        return redirect()->route('playlists.show', $playlist->id)->with('success', __('messages.playlist_created'));
    }

    /**
     * Display the specified playlist.
     */
    public function show(int $id): View
    {
        $playlist = Playlist::with(['user', 'songs.artist', 'songs.genre'])->findOrFail($id);

        $availableSongs = Song::with('artist')
            ->whereNotIn('id', $playlist->songs->pluck('id'))
            ->orderBy('title')
            ->get();

        return view('playlists.show', compact('playlist', 'availableSongs'));
    }

    /**
     * Show form for editing playlist.
     */
    public function edit(int $id): View
    {
        // hasMany() -> Auth kullanıcının bu playlist-e eýeçiligini barlamak
        $playlist = Auth::user()->playlists()->findOrFail($id);

        return view('playlists.edit', compact('playlist'));
    }

    /**
     * Update the playlist in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        // hasMany() -> Auth::user()->playlists() arkaly eýeçiligi barla
        $playlist = Auth::user()->playlists()->findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'is_public'   => 'nullable|boolean',
            'cover_image' => 'nullable|string',
        ]);

        $playlist->name        = $request->input('name');
        $playlist->description = $request->input('description');
        $playlist->is_public   = (bool) $request->input('is_public', true);

        if ($request->filled('cover_image')) {
            $playlist->cover_image = $request->input('cover_image');
        }

        $playlist->save();

        return redirect()->route('playlists.show', $playlist->id)
            ->with('success', __('messages.playlist_name') . ' ' . __('messages.save'));
    }

    /**
     * Remove the specified playlist from storage.
     * hasMany() -> Auth::user()->playlists()->findOrFail() bilen eýeçilik barlagly öçürmek
     */
    public function destroy(int $id): RedirectResponse
    {
        // hasMany() -> Diňe ulanyjynyň öz playlist-ini tapýarys (403 garşy goranyş)
        $playlist = Auth::user()->playlists()->findOrFail($id);

        $playlist->delete();

        return redirect()->route('playlists.index')->with('success', __('messages.song_deleted'));
    }

    /**
     * Add a song to the playlist.
     * hasMany() -> Auth::user()->playlists() arkaly eýeçilik barlagly aýdym goşmak
     */
    public function addSong(Request $request, int $id): RedirectResponse
    {
        // hasMany() -> Diňe ulanyjynyň öz playlist-ini tapýarys
        $playlist = Auth::user()->playlists()->findOrFail($id);

        $request->validate([
            'song_id' => 'required|exists:songs,id',
        ]);

        $songId = $request->input('song_id');
        $playlist->songs()->syncWithoutDetaching([$songId]);

        return back()->with('success', __('messages.song_added_to_playlist'));
    }

    /**
     * Remove a song from the playlist.
     * hasMany() -> Auth::user()->playlists() arkaly eýeçilik barlagly aýdym aýyrmak
     */
    public function removeSong(int $playlistId, int $songId): RedirectResponse
    {
        // hasMany() -> Diňe ulanyjynyň öz playlist-ini tapýarys
        $playlist = Auth::user()->playlists()->findOrFail($playlistId);

        $playlist->songs()->detach($songId);

        return back()->with('info', __('messages.song_removed_from_playlist'));
    }
}
