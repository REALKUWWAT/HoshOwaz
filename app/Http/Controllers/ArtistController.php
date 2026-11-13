<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ArtistController extends Controller
{
    /**
     * Display a listing of artists.
     * hasMany() -> withCount('songs') ulanylyp her sazandanyň aýdym sany görkezilýär.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        // hasMany() -> withCount: Artist modelindäki songs() hasMany relýasiýasyny sanlaýarys
        $query = Artist::withCount('songs')->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
        }

        $artists = $query->paginate(12)->withQueryString();

        return view('artists.index', compact('artists', 'search'));
    }

    /**
     * Show the form for creating a new artist.
     */
    public function create(): View
    {
        return view('artists.create');
    }

    /**
     * Store a newly created artist in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:150',
            'bio'        => 'nullable|string',
            'country'    => 'nullable|string|max:100',
            'image'      => 'nullable|string',
            'image_file' => 'nullable|image|max:5120',
        ]);

        $name    = $request->input('name');
        $bio     = $request->input('bio');
        $country = $request->input('country') ?: 'Türkmenistan';
        $image   = $request->input('image');

        if ($request->hasFile('image_file')) {
            $path  = $request->file('image_file')->store('artists', 'public');
            $image = asset('storage/' . $path);
        }

        if (!$image) {
            $image = asset('images/artists/default_avatar.svg');
        }

        $artist = Artist::create([
            'name'    => $name,
            'bio'     => $bio,
            'country' => $country,
            'image'   => $image,
        ]);

        return redirect()->route('artists.show', $artist->id)->with('success', __('messages.artist_created'));
    }

    /**
     * Display the specified artist with their songs.
     * hasMany() -> $artist->songs() — Artist modeliniň hasMany relýasiýasy arkaly
     * sazandanyň ähli aýdymlaryny, öne çykarylanlary we iň soňkulary getirýäris.
     */
    public function show(int $id): View
    {
        // hasMany() -> eager load bilen aýdymlaryny getirýäris
        $artist = Artist::with([
            'songs'        => fn($q) => $q->with('genre')->latest(),
            'featuredSongs'=> fn($q) => $q->with('genre'),
            'latestSongs'  => fn($q) => $q->with('genre'),
        ])->withCount('songs')->findOrFail($id);

        // hasMany() -> $artist->songs() — has relýasiýa arkaly aýdymlary alýarys
        $allSongs     = $artist->songs()->with('genre')->latest()->get();

        // hasMany() -> featuredSongs() — öne çykarylan aýdymlar
        $featuredSongs = $artist->featuredSongs()->with('genre')->get();

        // hasMany() -> aýdymlaryň diňleniş sany boýunça iň meşhurlar
        $popularSongs = $artist->songs()->with('genre')->orderByDesc('plays_count')->take(5)->get();

        // hasMany() -> genre boýunça sazandanyň aýdymlaryny toparlara bölmek
        $songsByGenre = $artist->songs()
            ->with('genre')
            ->get()
            ->groupBy(fn($song) => $song->genre?->name ?? 'Beýlekiler');

        return view('artists.show', compact(
            'artist',
            'allSongs',
            'featuredSongs',
            'popularSongs',
            'songsByGenre'
        ));
    }

    /**
     * Show the form for editing the artist.
     */
    public function edit(int $id): View
    {
        $artist = Artist::findOrFail($id);

        return view('artists.edit', compact('artist'));
    }

    /**
     * Update the artist in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $artist = Artist::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:150',
            'bio'        => 'nullable|string',
            'country'    => 'nullable|string|max:100',
            'image'      => 'nullable|string',
            'image_file' => 'nullable|image|max:5120',
        ]);

        $artist->name    = $request->input('name');
        $artist->bio     = $request->input('bio');
        $artist->country = $request->input('country', $artist->country);

        if ($request->filled('image')) {
            $artist->image = $request->input('image');
        }

        if ($request->hasFile('image_file')) {
            $path         = $request->file('image_file')->store('artists', 'public');
            $artist->image = asset('storage/' . $path);
        }

        $artist->save();

        return redirect()->route('artists.show', $artist->id)->with('success', __('messages.artist_created'));
    }

    /**
     * Remove the specified artist from storage.
     * hasMany() -> sazandany öçürende oňa degişli aýdymlar hem täzelener (DB kaskady ýa-da null).
     */
    public function destroy(int $id): RedirectResponse
    {
        $artist = Artist::findOrFail($id);

        // hasMany() -> sazandanyň aýdymlaryny artist_id = null ediň (ýörite aýrylmazdan öň)
        $artist->songs()->update(['artist_id' => null]);

        $artist->delete();

        return redirect()->route('artists.index')->with('success', __('messages.confirm_delete'));
    }
}
