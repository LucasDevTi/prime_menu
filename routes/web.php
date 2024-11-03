<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

Route::post('auth', action: [AuthController::class, 'auth'])->name('auth');

Route::get('/', function () {
    return view('layouts/login');
});

Route::get('/gestao', [HomeController::class, 'index'])->name('gestao');
// Route::get('/register', [AuthController::class, 'register'])->name('register');

Route::post('/buscar-cliente-telefone', [ClientController::class, 'findByCel'])->name('find-client-cel');
Route::post('/setreserva', [ReservationController::class, 'setReserva'])->name('setReserva');
Route::get('/get-status-mesa', [TableController::class, 'getStatusMesa'])->name('get-status-mesa');
Route::post('/atualizar-status-mesa', [TableController::class, 'atualizarStatusMesa'])->name('atualizar-status-mesa');

