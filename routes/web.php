<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\PlaylistController;

/*
|--------------------------------------------------------------------------
| Web Routes - Hoşowaz Music Streaming Platform
|--------------------------------------------------------------------------
|
| High performance MVC routes matching core application requirements:
| Homepage, Language Switcher, Authentication & Password Recovery,
| Song CRUD, Artist CRUD, and Playlist Management.
|
*/

// 1. Homepage & Global Language Switcher
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/lang/{locale}', [HomeController::class, 'switchLanguage'])->name('lang.switch');

// 2. Authentication & 6-Digit Email Verification Password Recovery
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
    Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])->name('password.code.send');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset.show');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// 3. Songs Management (CRUD & Player endpoint)
Route::prefix('songs')->name('songs.')->group(function () {
    Route::get('/', [SongController::class, 'index'])->name('index');
    Route::get('/create', [SongController::class, 'create'])->name('create');
    Route::post('/', [SongController::class, 'store'])->name('store');
    Route::get('/{id}', [SongController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [SongController::class, 'edit'])->name('edit');
    Route::put('/{id}', [SongController::class, 'update'])->name('update');
    Route::delete('/{id}', [SongController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/play', [SongController::class, 'play'])->name('play');
});

// 4. Artists Management (CRUD)
Route::prefix('artists')->name('artists.')->group(function () {
    Route::get('/', [ArtistController::class, 'index'])->name('index');
    Route::get('/create', [ArtistController::class, 'create'])->name('create');
    Route::post('/', [ArtistController::class, 'store'])->name('store');
    Route::get('/{id}', [ArtistController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ArtistController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ArtistController::class, 'update'])->name('update');
    Route::delete('/{id}', [ArtistController::class, 'destroy'])->name('destroy');
});

// 5. Playlists Management
Route::prefix('playlists')->name('playlists.')->group(function () {
    Route::get('/', [PlaylistController::class, 'index'])->name('index');
    Route::get('/create', [PlaylistController::class, 'create'])->name('create');
    Route::post('/', [PlaylistController::class, 'store'])->name('store');
    Route::get('/{id}', [PlaylistController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [PlaylistController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PlaylistController::class, 'update'])->name('update');
    Route::delete('/{id}', [PlaylistController::class, 'destroy'])->name('destroy');

    // Playlist song management
    Route::post('/{id}/songs', [PlaylistController::class, 'addSong'])->name('songs.add');
    Route::delete('/{playlistId}/songs/{songId}', [PlaylistController::class, 'removeSong'])->name('songs.remove');
});
