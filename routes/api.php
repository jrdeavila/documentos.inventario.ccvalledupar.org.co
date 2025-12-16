<?php

use App\Http\Controllers\SearchController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketExportController;
use Illuminate\Support\Facades\Route;


Route::get('/tickets/export', TicketExportController::class)->name('tickets.export');
Route::apiResource('tickets', TicketController::class)->names('tickets');
Route::get('/search', SearchController::class)->name('search');
Route::fallback(fn() => response()->json(['message' => 'Ruta no encontrada'], 404));
