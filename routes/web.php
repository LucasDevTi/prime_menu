<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::post('auth', action: [AuthController::class, 'auth'])->name('auth');

Route::get('/', function () {
    return view('layouts/login');
});

Route::get('/gestao', [HomeController::class, 'index'])->name('gestao');
// Route::get('/register', [AuthController::class, 'register'])->name('register');
