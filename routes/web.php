<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ActivityTrailController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\NotificationCenterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\ZabbixConnectionController;
use App\Http\Controllers\MonitoringGraphController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/monitoring/graph/{graph}',
    [MonitoringGraphController::class, 'show']
)->name('monitoring.graph.image');

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware(['auth', 'verified']);

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('companies', CompanyController::class)->except(['show']);
    Route::resource('sites', SiteController::class)->except(['show']);
    Route::resource('devices', DeviceController::class)->except(['show']);
    Route::resource('zabbix-connections', ZabbixConnectionController::class)->except(['show']);

    Route::get('/notifications', [NotificationCenterController::class, 'index'])
        ->middleware('permission:monitoring.view')
        ->name('notifications.index');

    Route::get('/activity-trail', [ActivityTrailController::class, 'index'])
        ->middleware('permission:company.manage')
        ->name('activity-trail.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'permission:monitoring.view'])
    ->prefix('monitoring')
    ->name('monitoring.')
    ->group(function () {
        Route::get('/', [MonitoringController::class, 'overview'])->name('overview');
        Route::get('/problems', [MonitoringController::class, 'problems'])->name('problems');
        Route::get('/events', [MonitoringController::class, 'events'])->name('events');
        Route::get('/hosts', [MonitoringController::class, 'hosts'])->name('hosts');
        Route::get('/availability', [MonitoringController::class, 'availability'])->name('availability');
        Route::get('/graphs', [MonitoringController::class, 'graphs'])->name('graphs');

        Route::post('/problems/acknowledge', [MonitoringController::class, 'acknowledgeProblem'])
            ->middleware('permission:problem.acknowledge')
            ->name('problems.acknowledge');
    });

require __DIR__.'/auth.php';

use App\Http\Controllers\WireGuardController;

Route::get('/wireguard/peers', [WireGuardController::class, 'peers']);
