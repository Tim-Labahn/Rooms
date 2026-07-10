<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Room;

use App\Http\Controllers\RoomController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\BookingController;

// Redirect root → /login
Route::get('/', function () {
    return redirect()->route('login');
});

// Show login page
Route::get('/login', function () {
    return view('login');
})->name('login');

// Registration routes
Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.submit');

// Handle login form submission
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/home', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/search-suggestions', [\App\Http\Controllers\HomeController::class, 'searchSuggestions'])->name('search.suggestions');

    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show')->where('room', '[0-9]+');
    Route::get('/rooms/{room}/book', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::post('/bookings/{booking}/end', [BookingController::class, 'end'])->name('bookings.end');
    Route::post('/bookings/{booking}/extend', [BookingController::class, 'extend'])->name('bookings.extend');

    Route::get('/rooms/{room}/toggle', [RoomController::class, 'toggle'])->name('rooms.toggle');

    Route::get('/user', [\App\Http\Controllers\UserController::class, 'index'])->name('user.profile');
    Route::post('/user', [\App\Http\Controllers\UserController::class, 'update'])->name('user.update');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/overview', [\App\Http\Controllers\HomeController::class, 'adminOverview'])->name('admin.overview');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

    Route::get('/features', [FeatureController::class, 'index'])->name('features.index');
    Route::post('/features', [FeatureController::class, 'store'])->name('features.store');
    Route::put('/features/{feature}', [FeatureController::class, 'update'])->name('features.update');
    Route::delete('/features/{feature}', [FeatureController::class, 'destroy'])->name('features.destroy');

    Route::put('/sections/{section}', [\App\Http\Controllers\SectionController::class, 'update'])->name('sections.update');
    Route::post('/sections', [\App\Http\Controllers\SectionController::class, 'store'])->name('sections.store');
    Route::post('/buildings', [\App\Http\Controllers\BuildingController::class, 'store'])->name('buildings.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
