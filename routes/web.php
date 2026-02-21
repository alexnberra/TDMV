<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('marketing/HomePage');
})->name('home');

Route::get('/features', fn () => Inertia::render('marketing/FeaturesPage'))->name('features');
Route::get('/pricing', fn () => Inertia::render('marketing/PricingPage'))->name('pricing');
Route::get('/about', fn () => Inertia::render('marketing/AboutPage'))->name('about');
Route::get('/contact', fn () => Inertia::render('marketing/ContactPage'))->name('contact');
Route::get('/login', fn () => Inertia::render('marketing/LoginPage'))->name('login');
Route::get('/register', fn () => Inertia::render('marketing/RegisterPage'))->name('register');
Route::get('/dashboard', fn () => Inertia::render('portal/Dashboard'))
    ->middleware('auth')
    ->name('dashboard');

Route::redirect('/app', '/portal');

Route::prefix('portal')->group(function () {
    Route::get('/', fn () => Inertia::render('portal/Dashboard'))->name('portal.dashboard');
    Route::get('/service-selector', fn () => Inertia::render('portal/ServiceSelector'))->name('portal.service-selector');
    Route::get('/requirements', fn () => Inertia::render('portal/RequirementsChecklist'))->name('portal.requirements');
    Route::get('/upload', fn () => Inertia::render('portal/DocumentUpload'))->name('portal.upload');
    Route::get('/review', fn () => Inertia::render('portal/ReviewPayment'))->name('portal.review');
    Route::get('/status/{id}', fn (string $id) => Inertia::render('portal/ApplicationStatus', ['id' => $id]))->name('portal.status');
    Route::get('/vehicle/{id}', fn (string $id) => Inertia::render('portal/VehicleProfile', ['id' => $id]))->name('portal.vehicle');
    Route::get('/phase-2a', fn () => Inertia::render('portal/Phase2aOps'))->name('portal.phase-2a');
    Route::get('/phase-2b', fn () => Inertia::render('portal/Phase2bOps'))->name('portal.phase-2b');
    Route::get('/phase-3', fn () => Inertia::render('portal/Phase3Ops'))->name('portal.phase-3');
    Route::get('/notifications', fn () => Inertia::render('portal/Notifications'))->name('portal.notifications');
    Route::get('/support', fn () => Inertia::render('portal/Support'))->name('portal.support');
    Route::get('/admin', fn () => Inertia::render('portal/AdminDashboard'))->name('portal.admin');
});

Route::get('/vehicle/{id}', fn (string $id) => Inertia::render('portal/VehicleProfile', ['id' => $id]))
    ->name('vehicle.profile');

require __DIR__.'/settings.php';
