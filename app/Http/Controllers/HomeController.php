<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /**
     * Display the main streaming dashboard.
     * hasMany() relýasiýalary arkaly maglumatlar getirýäris:
     *  - Artist::songs() hasMany
     *  - Genre::songs()  hasMany
     *  - User::playlists() hasMany
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        if ($search) {
            $songs = Song::with(['artist', 'genre'])
                ->where('title', 'like', "%{$search}%")
                ->orWhereHas('artist', fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->orWhereHas('genre',  fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->latest()
                ->paginate(12);

            return view('songs.index', [
                'songs'         => $songs,
                'search'        => $search,
                'selectedGenre' => null,
                'genres'        => Genre::all(),
            ]);
        }

        // hasMany() -> Artist::withCount('songs') — Artist modelindäki songs() hasMany relýasiýasy
        $popularArtists = Artist::withCount('songs')
            ->orderByDesc('songs_count')
            ->take(5)
            ->get();

        // hasMany() -> Genre::withCount('songs') — Genre modelindäki songs() hasMany relýasiýasy
        $genres = Genre::withCount('songs')->get();

        // hasMany() -> Genre->songs() bilen her žanrdaky meşhur aýdymlar
        $genresWithSongs = Genre::with([
            'popularSongs' => fn($q) => $q->with('artist')->take(3),
        ])->withCount('songs')->get();

        // Öne çykarylan aýdymlar
        $featuredSongs = Song::with(['artist', 'genre'])
            ->where('is_featured', true)
            ->latest()
            ->take(6)
            ->get();

        // hasMany() -> User::playlists() — giriş eden ulanyjynyň öz saznamalary
        $topPlaylists = Playlist::with(['user', 'songs'])
            ->where('is_public', true)
            ->latest()
            ->take(4)
            ->get();

        // Iň soňky aýdymlar
        $recentSongs = Song::with(['artist', 'genre'])->latest()->take(8)->get();

        // hasMany() -> Authenticated user's playlists via hasMany relýasiýasy
        $myPlaylists = Auth::check()
            ? Auth::user()->playlists()->latest()->take(5)->get()
            : collect();

        return view('home', compact(
            'featuredSongs',
            'popularArtists',
            'topPlaylists',
            'genres',
            'genresWithSongs',
            'recentSongs',
            'myPlaylists'
        ));
    }

    /**
     * Switch application language.
     */
    public function switchLanguage(string $locale): RedirectResponse
    {
        $allowed = ['tk', 'en', 'ru', 'tr'];

        if (in_array($locale, $allowed)) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }

        return redirect()->back();
    }
}
