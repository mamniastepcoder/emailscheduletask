<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderConfirmController;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/order-mail', [OrderConfirmController::class, 'index']);
