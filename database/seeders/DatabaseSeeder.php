<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Song;
use App\Models\Playlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Users (with local offline avatar)
        $admin = User::create([
            'name' => 'Merdan Hojayew',
            'email' => 'admin@hoshowaz.com',
            'password' => Hash::make('password123'),
            'avatar' => '/images/artists/default_avatar.svg',
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'name' => 'Aylar Berdiyewa',
            'email' => 'aylar@hoshowaz.com',
            'password' => Hash::make('password123'),
            'avatar' => '/images/artists/default_avatar.svg',
            'email_verified_at' => now(),
        ]);

        // 2. Create Artists (with local offline SVG images)
        $artists = [
            [
                'name' => 'Sahydursun Hojakowa',
                'bio' => 'Türkmenistanyň halk artisti, milli aýdymlaryň we estrada sungatynyň ussady.',
                'image' => '/images/artists/artist1.svg',
                'country' => 'Türkmenistan',
            ],
            [
                'name' => 'Myrat Öwez',
                'bio' => 'Meşhur estrada aýdymçysy, häzirki zaman türkmen pop aýdymlarynyň ýerine ýetirijisi.',
                'image' => '/images/artists/artist2.svg',
                'country' => 'Türkmenistan',
            ],
            [
                'name' => 'Gözel Annamuhammedowa',
                'bio' => 'Ýakymly owazy bilen halkyň söýgüsini gazanan estrada aýdymçysy.',
                'image' => '/images/artists/artist3.svg',
                'country' => 'Türkmenistan',
            ],
            [
                'name' => 'Parahat Pürjäýew',
                'bio' => 'Halk aýdymlaryny we dessanlary döwrebap sazlar bilen sazlaşdyrýan bagşy.',
                'image' => '/images/artists/artist4.svg',
                'country' => 'Türkmenistan',
            ],
            [
                'name' => 'Eldar Ahmedow',
                'bio' => 'Ýaşlaryň arasynda uly meşhurlyga eýe bolan lirik aýdymlaryň ussady.',
                'image' => '/images/artists/artist5.svg',
                'country' => 'Türkmenistan',
            ],
        ];

        $createdArtists = [];
        foreach ($artists as $item) {
            $createdArtists[] = Artist::create($item);
        }

        // 3. Create Genres
        $genres = [
            [
                'name' => 'Pop',
                'slug' => 'pop',
                'color' => 'from-pink-500 to-rose-600',
                'icon' => 'sparkles',
            ],
            [
                'name' => 'Halk Aýdymlary',
                'slug' => 'folk',
                'color' => 'from-amber-500 to-orange-600',
                'icon' => 'globe',
            ],
            [
                'name' => 'Estrada',
                'slug' => 'estrada',
                'color' => 'from-purple-600 to-indigo-600',
                'icon' => 'music',
            ],
            [
                'name' => 'Akustik',
                'slug' => 'acoustic',
                'color' => 'from-teal-500 to-emerald-600',
                'icon' => 'guitar',
            ],
            [
                'name' => 'Dutar & Saz',
                'slug' => 'dutar',
                'color' => 'from-cyan-500 to-blue-600',
                'icon' => 'disc',
            ],
        ];

        $createdGenres = [];
        foreach ($genres as $genre) {
            $createdGenres[] = Genre::create($genre);
        }

        // 4. Create Songs (with local offline playable WAV audio and local cover SVGs)
        $songsData = [
            [
                'title' => 'Owadan Diýar',
                'artist_id' => $createdArtists[0]->id,
                'genre_id' => $createdGenres[1]->id,
                'user_id' => $admin->id,
                'duration' => '3:45',
                'audio_path' => '/audio/owadan_diyar.wav',
                'cover_image' => '/images/covers/cover1.svg',
                'lyrics' => "Gül açylýar baglarda,\nOwaz ýaňlanýar daglarda.\nEziz Watan, gözel mekan,\nBagt paýlaýar çaglarda.",
                'plays_count' => 1240,
                'is_featured' => true,
            ],
            [
                'title' => 'Söýgi Aýdymy',
                'artist_id' => $createdArtists[1]->id,
                'genre_id' => $createdGenres[0]->id,
                'user_id' => $admin->id,
                'duration' => '3:20',
                'audio_path' => '/audio/soygi_aydymy.wav',
                'cover_image' => '/images/covers/cover2.svg',
                'lyrics' => "Gözleriňde ýyldyzlar,\nÝylgyryşyň nur saçar.\nSensiz geçmez günlerim,\nGaraşýaryn her säher.",
                'plays_count' => 3890,
                'is_featured' => true,
            ],
            [
                'title' => 'Ýaşlyk Bahary',
                'artist_id' => $createdArtists[2]->id,
                'genre_id' => $createdGenres[2]->id,
                'user_id' => $admin->id,
                'duration' => '4:02',
                'audio_path' => '/audio/yashlyk_bahary.wav',
                'cover_image' => '/images/covers/cover3.svg',
                'lyrics' => "Baharyň sergin şemaly,\nÝatladýar geçmiş ýyllary.\nŞatlyk doly kalbymyzda,\nÝaşlygyň joşgunly aýdymlary.",
                'plays_count' => 2150,
                'is_featured' => true,
            ],
            [
                'title' => 'Dutar Mukamy',
                'artist_id' => $createdArtists[3]->id,
                'genre_id' => $createdGenres[4]->id,
                'user_id' => $user2->id,
                'duration' => '5:12',
                'audio_path' => '/audio/dutar_mukamy.wav',
                'cover_image' => '/images/covers/cover4.svg',
                'lyrics' => "Kakylynda dutaryň,\nKöňül syry açylýar.\nTürkmen sährasyna saba,\nNurly şugla saçylýar.",
                'plays_count' => 4500,
                'is_featured' => true,
            ],
            [
                'title' => 'Ýodalar',
                'artist_id' => $createdArtists[4]->id,
                'genre_id' => $createdGenres[3]->id,
                'user_id' => $user2->id,
                'duration' => '3:15',
                'audio_path' => '/audio/yodalar.wav',
                'cover_image' => '/images/covers/cover5.svg',
                'lyrics' => "Ýollar uzap gidýär alys,\nÝürekde bar täze arzuw.\nHer ädimde ümit bar,\nGeljege bolan uly ynam.",
                'plays_count' => 1760,
                'is_featured' => false,
            ],
            [
                'title' => 'Aşgabat Agşamlary',
                'artist_id' => $createdArtists[1]->id,
                'genre_id' => $createdGenres[0]->id,
                'user_id' => $admin->id,
                'duration' => '3:50',
                'audio_path' => '/audio/ashgabat_agsamlary.wav',
                'cover_image' => '/images/covers/cover6.svg',
                'lyrics' => "Ak mermerli şäherim,\nÇyralary lowurdaýar.\nAgşamara seýilgähde,\nSöýgi aýdymlary ýaňlanýar.",
                'plays_count' => 5200,
                'is_featured' => true,
            ],
            [
                'title' => 'Küştdepdi Joşguny',
                'artist_id' => $createdArtists[0]->id,
                'genre_id' => $createdGenres[1]->id,
                'user_id' => $admin->id,
                'duration' => '4:30',
                'audio_path' => '/audio/kushtdepdi.wav',
                'cover_image' => '/images/covers/cover7.svg',
                'lyrics' => "Küştdepdiniň ritminde,\nJoşup tans edýär ýaşlar.\nHalkyň gadymy sungaty,\nNesilden-nesle ýaşaýar.",
                'plays_count' => 6100,
                'is_featured' => true,
            ],
        ];

        $createdSongs = [];
        foreach ($songsData as $s) {
            $createdSongs[] = Song::create($s);
        }

        // 5. Create Playlists
        $playlist1 = Playlist::create([
            'user_id' => $admin->id,
            'name' => 'Iň Köp Diňlenilýänler (Top Hits)',
            'description' => 'Hoşowaz platformasynda iň köp diňlenilen iň gowy aýdymlar.',
            'cover_image' => '/images/covers/cover1.svg',
            'is_public' => true,
        ]);
        $playlist1->songs()->attach([$createdSongs[0]->id, $createdSongs[1]->id, $createdSongs[3]->id, $createdSongs[5]->id]);

        $playlist2 = Playlist::create([
            'user_id' => $admin->id,
            'name' => 'Gijeki dynç alyş (Chill Acoustic)',
            'description' => 'Agşamky ýakymly dynç alyş üçin rahatlandyryjy mukamlar.',
            'cover_image' => '/images/covers/cover5.svg',
            'is_public' => true,
        ]);
        $playlist2->songs()->attach([$createdSongs[2]->id, $createdSongs[4]->id]);

        $playlist3 = Playlist::create([
            'user_id' => $user2->id,
            'name' => 'Milli Miras (Folk & Dutar)',
            'description' => 'Dutar we dessan aýdymlarynyň baý toplumy.',
            'cover_image' => '/images/covers/cover4.svg',
            'is_public' => true,
        ]);
        $playlist3->songs()->attach([$createdSongs[0]->id, $createdSongs[3]->id, $createdSongs[6]->id]);
    }
}
