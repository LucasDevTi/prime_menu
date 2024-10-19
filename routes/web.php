<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

Route::post('auth', action: [AuthController::class, 'auth'])->name('auth');

Route::get('/', function () {
    return view('layouts/login');
});

Route::get('/gestao', [HomeController::class, 'index'])->name('gestao');
// Route::get('/register', [AuthController::class, 'register'])->name('register');

Route::post('/buscar-cliente-telefone', [ClientController::class, 'findByCel'])->name('find-client-cel');
Route::post('/setreserva',[ReservaController::class, 'setReserva'])->name('setReserva');