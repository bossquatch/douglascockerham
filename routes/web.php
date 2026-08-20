<?php

use App\Http\Controllers\InstructorAdminController;
use App\Http\Controllers\InstructorIntakeController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/em/training', [InstructorIntakeController::class, 'create'])
    ->name('instructors.create');
Route::post('/em/training', [InstructorIntakeController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('instructors.store');
Route::get('/em/training/thank-you', [InstructorIntakeController::class, 'success'])
    ->name('instructors.success');
Route::redirect('/region7/instructors', '/em/training');

Route::get('/em/classes/g300', function () {
	return view('g300');
})->name('g300');

Route::get('/em/classes/g400', function () {
	return view('g400');
})->name('g400');

Route::middleware(['auth'])->group(function () {
    Route::redirect('/region7/instructors/admin', '/em/training/admin');
    Route::redirect('dashboard', '/em/training/admin')->name('dashboard');
    Route::redirect('inventory', '/em/training/admin')->name('inventory');

    Route::prefix('em/training/admin')->name('instructors.admin.')->group(function () {
        Route::get('/', [InstructorAdminController::class, 'index'])->name('index');
        Route::get('/export', [InstructorAdminController::class, 'export'])->name('export');
        Route::patch('/capabilities/{capability}', [InstructorAdminController::class, 'update'])->name('update');
    });

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('/board', function () {
	return view('shelter_board');
})->name('board');

require __DIR__.'/auth.php';
