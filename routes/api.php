<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CreateManyTicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketExportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
  Route::get('/audit', [AuditLogController::class, 'index']);
  Route::get('/audit/{id}', [AuditLogController::class, 'show']);
  Route::middleware('audit')->group(function () {
    Route::get('/profile', ProfileController::class)->name('profile');
    Route::post('tickets/many', CreateManyTicketController::class)->name('tickets.many');
    Route::get('/tickets/export', TicketExportController::class)->name('tickets.export');
    Route::apiResource('tickets', TicketController::class)->names('tickets');
    Route::get('/search', SearchController::class)->name('search');
  });
});

Route::fallback(fn() => response()->json(['message' => 'Ruta no encontrada'], 404));
