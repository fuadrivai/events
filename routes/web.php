<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Dashboard\EventController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\PublicEventRegistrationController;
use App\Http\Controllers\AdminEventRegistrationController;
use App\Http\Controllers\DashboardEventRegistrationController;
use App\Http\Controllers\EventPriceOptionController;
use App\Http\Controllers\Webhooks\XenditInvoiceWebhookController;
use App\Http\Controllers\Dashboard\EventEmailTemplateController;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\EventRegistrationGoogleSheetExportController;
use App\Http\Controllers\Dashboard\PhotoEventController;
use App\Http\Controllers\PhotoDownloadController;
use App\Http\Controllers\PhotoUploadController;
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [EventController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard/events', [EventController::class, 'index'])->name('dashboard.events');

    Route::get('/dashboard/events/create', [EventController::class, 'create'])
        ->name('dashboard.events.create');
    Route::get('/dashboard/events/{event}', [EventController::class, 'show'])
    ->name('dashboard.events.show');
    Route::post('/dashboard/events', [EventController::class, 'store'])
        ->name('dashboard.events.store');
    Route::get('/dashboard/events/{event}/fields', [EventController::class, 'fields'])
    ->name('dashboard.events.fields');
    Route::get('/dashboard/events/{event}/fields/create', [EventController::class, 'fieldsCreate'])
    ->name('dashboard.events.fields.create');

    Route::post('/dashboard/events/{event}/fields', [EventController::class, 'fieldsStore'])
    ->name('dashboard.events.fields.store');
    Route::get('/dashboard/events/{event}/fields/{field}/edit', [EventController::class, 'fieldsEdit'])
    ->name('dashboard.events.fields.edit');

    Route::put('/dashboard/events/{event}/fields/{field}', [EventController::class, 'fieldsUpdate'])
    ->name('dashboard.events.fields.update');
    Route::delete('/dashboard/events/{event}/fields/{field}', [EventController::class, 'fieldsDestroy'])
    ->name('dashboard.events.fields.destroy');
    Route::post('/dashboard/events/{event}/fields/reorder', [EventController::class, 'fieldsReorder'])
    ->name('dashboard.events.fields.reorder');
    Route::get('/dashboard/events/{event}/edit', [EventController::class, 'edit'])->name('dashboard.events.edit');
    Route::put('/dashboard/events/{event}', [EventController::class, 'update'])->name('dashboard.events.update');
    Route::get('/dashboard/events/{event}/registrations', [DashboardEventRegistrationController::class, 'index'])
        ->name('dashboard.events.registrations.index');

    Route::get('/dashboard/registrations/{registration}', [DashboardEventRegistrationController::class, 'show'])
        ->name('dashboard.registrations.show');
    Route::get('/org/{orgSlug}/events/{eventId}/prices', [EventPriceOptionController::class, 'index'])->name('prices.index');
    Route::post('/org/{orgSlug}/events/{eventId}/prices', [EventPriceOptionController::class, 'store'])->name('prices.store');
    Route::put('/org/{orgSlug}/events/{eventId}/prices/{priceId}', [EventPriceOptionController::class, 'update'])->name('prices.update');
    Route::post('/org/{orgSlug}/events/{eventId}/prices/{priceId}/toggle', [EventPriceOptionController::class, 'toggle'])->name('prices.toggle');
    Route::post('/org/{orgSlug}/events/{eventId}/prices/reorder', [EventPriceOptionController::class, 'reorder'])->name('prices.reorder');
    Route::get(
        '/org/{orgSlug}/events/{eventId}/email-templates',
        [EventEmailTemplateController::class, 'index']
    )->name('admin.events.email-templates.index');

    Route::post(
        '/org/{orgSlug}/events/{eventId}/email-templates',
        [EventEmailTemplateController::class, 'store']
    )->name('admin.events.email-templates.store');
    Route::post('/org/{orgSlug}/events/{eventId}/email-templates/preview', [EventEmailTemplateController::class, 'preview'])
        ->whereNumber('eventId')
        ->name('admin.events.email-templates.preview');

});
Route::middleware(['auth'])->group(function () {
    // connect owner (jalanin sekali pakai akun owner)
    Route::get('/google/connect', [GoogleOAuthController::class, 'connect'])->name('google.connect');
    Route::get('/google/callback', [GoogleOAuthController::class, 'callback'])->name('google.callback');

    // export (share ke user login)
    Route::post('/events/{eventId}/registrations/export-google-sheet', [EventRegistrationGoogleSheetExportController::class, 'export'])
        ->name('events.registrations.export_google_sheet');
});
Route::get('/e/{eventSlug}', [PublicEventController::class, 'show'])
    ->name('public.event.show');
Route::middleware(['auth','admin'])->prefix('admin')->group(function () {
    Route::get('/users/create', [AdminUserController::class, 'create']);
    Route::post('/users', [AdminUserController::class, 'store']);
});
Route::middleware(['auth', 'admin'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {
        Route::get('/users', [\App\Http\Controllers\Dashboard\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [\App\Http\Controllers\Dashboard\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [\App\Http\Controllers\Dashboard\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\Dashboard\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [\App\Http\Controllers\Dashboard\UserController::class, 'update'])->name('users.update');
        Route::get('/photo-events', [PhotoEventController::class, 'index'])->name('photo-events.index');
        Route::get('/photo-events/create', [PhotoEventController::class, 'create'])->name('photo-events.create');
        Route::post('/photo-events', [PhotoEventController::class, 'store'])->name('photo-events.store');
        Route::get('/photo-events/{photoEvent}/edit', [PhotoEventController::class, 'edit'])->name('photo-events.edit');
        Route::put('/photo-events/{photoEvent}', [PhotoEventController::class, 'update'])->name('photo-events.update');
        Route::get('/photo-events/{photoEvent}/photos/{photo}/preview', [PhotoEventController::class, 'photoPreview'])
            ->name('photo-events.photos.preview');
        Route::get('/photo-events/{photoEvent}/booth', [PhotoEventController::class, 'booth'])
            ->name('photo-events.booth');
        Route::post('/photo-events/{photoEvent}/photos', [PhotoUploadController::class, 'store'])
            ->name('photo-events.photos.store');
    });

Route::get('/photo-download/{token}', PhotoDownloadController::class)
    ->where('token', '[A-Za-z0-9]{64}')
    ->name('photo.download');

Route::view('/payment/success', 'payment.success')->name('payment.success');
Route::view('/payment/failed', 'payment.failed')->name('payment.failed');
Route::post('/webhooks/xendit/invoices', [XenditInvoiceWebhookController::class, 'handle']);
Route::post('/public/event/{event:slug}/register', [PublicEventRegistrationController::class, 'store'])
    ->name('public.event.register');
Route::post('/e/{eventSlug}/register', [PublicEventController::class, 'register']);
require __DIR__.'/auth.php';
