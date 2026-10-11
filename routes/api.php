<?php

use App\Http\Controllers\MonitoringController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [MonitoringController::class, 'health']);
Route::get('/files', [MonitoringController::class, 'files']);
Route::get('/events', [MonitoringController::class, 'events']);
Route::post('/watch', [MonitoringController::class, 'watch']);
Route::delete('/watch/{file}', [MonitoringController::class, 'unwatch'])->where('file', '.*');
Route::post('/demo/webhook', [MonitoringController::class, 'demoWebhook']);
Route::post('/events/{event}/replay', [MonitoringController::class, 'replay']);
Route::post('/events/{event}/retry', [MonitoringController::class, 'retry']);
Route::post('/events/{event}/acknowledge', [MonitoringController::class, 'acknowledge']);
Route::post('/events/{event}/resolve', [MonitoringController::class, 'resolve']);
Route::get('/metrics', [MonitoringController::class, 'metrics']);