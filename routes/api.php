<?php

use App\Http\Controllers\AuctionHuiDetailController;
use App\Http\Controllers\AuctionHuiRoomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserWinHuiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::get('images/users/{filename}', [ImageController::class, 'getImageUser'])->name('images.get');
Route::get('images/products/{filename}', [ImageController::class, 'getImageProduct'])->name('images.get');

Route::get('room/user/{userId}/{item}', [RoomController::class, 'getRoomsByUserId']);
Route::get('rooms/{item}', [RoomController::class, 'getAllRoomsWithUsers']);
Route::post('room/actionroom', [RoomUserController::class, 'addUserForRoom']);
Route::post('room/addroom', [RoomController::class, 'addroom']);
Route::get('room/detail/{id}', [RoomController::class, 'getDetailRoom']);
Route::post('message/postMess', [MessageController::class, 'postMessage']);
Route::get('message/room/{id}', [MessageController::class, 'getMessage']);
Route::get('notification/user/{id}', [NotificationController::class, 'getNotiByUser']);
Route::post('notification/post', [NotificationController::class, 'sendNotification']);
Route::delete('notification/remove/{id}', [NotificationController::class, 'removeNotiByUser']);
Route::post('auction-room/', [AuctionHuiRoomController::class, 'createAuctionHui']);
Route::post('auction-hui/user', [AuctionHuiDetailController::class, 'auctionHui']);
Route::get('auction-hui/hui-room/{id}', [AuctionHuiDetailController::class, 'getAuctionHui']);
Route::delete('auction-hui/remove/{id}', [AuctionHuiRoomController::class, 'removeAuctionHui']);
Route::get('auction-hui/user/win/{id}', [AuctionHuiDetailController::class, 'getTotal']);
Route::post('post-user-win', [AuctionHuiDetailController::class, 'postUserWin']);
Route::post('checkout', [CheckoutController::class, 'checkout']);
Route::get('rooms/count/{id}', [RoomController::class, 'getRoomsByCount']);
Route::get('rooms/price/{id}', [RoomController::class, 'getRoomsByPrice']);
Route::get('auction/user-win/{id}', [UserWinHuiController::class, 'getHuiByUser']);
Route::get('auction/{id}', [UserWinHuiController::class, 'updatePaid']);

Route::post('create-payment-link', [CheckoutController::class, 'createPaymentLink']);
Route::get('payment-info/{id}', [CheckoutController::class, 'getPaymentLinkInfoOfOrder']);
Route::get('payment-cancel/{id}', [CheckoutController::class, 'cancelPaymentLinkOfOrder']);


Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('login', [AuthController::class, 'login']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/update-avatar/{id}', [UserController::class, 'updateAvatar']);
    Route::post('/update-password/{user}', [UserController::class, 'updatePassword']);
    Route::post('/update-cccd/{id}', [UserController::class, 'updateCCCD']);
    Route::post('/update-info/{id}', [UserController::class, 'updateInfo']);
    Route::get('users/', [UserController::class, 'getAllUser']);
});
