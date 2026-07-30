<?php

use App\Http\Controllers\GabbyDashboardController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/gabby', [GabbyDashboardController::class, 'overview'])->name('gabby');
Route::get('/gabby/briefing', [GabbyDashboardController::class, 'briefing'])->name('gabby.briefing');
Route::get('/gabby/map', [GabbyDashboardController::class, 'map'])->name('gabby.map');
Route::get('/gabby/elections', [GabbyDashboardController::class, 'elections'])->name('gabby.elections');

Route::get('/em/classes/g300', function () {
	return view('g300');
})->name('g300');

Route::get('/em/classes/g400', function () {
	return view('g400');
})->name('g400');

Route::view('inventory', 'dashboard')
	->middleware(['auth', 'verified'])
	->name('inventory');
	
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('/board', function () {
	return view('shelter_board');
})->name('board');

require __DIR__.'/auth.php';
