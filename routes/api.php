<?php

use App\Http\Controllers\Api\TicketApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/open-tickets', [TicketApiController::class, 'openTickets'])->name('api.open-tickets');
    Route::get('/closed-tickets', [TicketApiController::class, 'closedTickets'])->name('api.closed-tickets');
    Route::get('/users/{email}/tickets', [TicketApiController::class, 'userTickets'])->name('api.user-tickets');
    Route::get('/stats', [TicketApiController::class, 'stats'])->name('api.stats');
});
