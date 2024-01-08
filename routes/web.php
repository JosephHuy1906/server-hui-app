<?php

use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::match(['get', 'post'], '/vnpay-callback', [CheckoutController::class, 'handleCallback']);

Route::post('/create-payment-link', [CheckoutController::class, 'createPaymentLink']);

Route::get('/success', function () {
    return view('success');
});

Route::get('/cancel', function () {
    return view('cancel');
});
