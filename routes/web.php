<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FactureController;
use App\Http\Middleware\CheckAuth;


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware([CheckAuth::class])->group(function () {

    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    Route::post('/process-excel', [FactureController::class, 'processExcel'])->name('process.excel');

});