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

// Route::match(['get', 'post'], '/vnpay-callback', [CheckoutController::class, 'handleCallback']);

Route::post('/create-payment-link', [CheckoutController::class, 'createPaymentLink']);

Route::match(['get', 'post'], '/success', [CheckoutController::class, 'handlePaymentSuccessAuctionHui']);

Route::match(['get', 'post'], '/cancel', [CheckoutController::class, 'handlePaymentCancelAuctionHui']);


Route::match(['get', 'post'], '/successRoom', [CheckoutController::class, 'handlePaymentSuccessRoom']);

Route::match(['get', 'post'], '/cancelRoom', [CheckoutController::class, 'handlePaymentCancelRoom']);
