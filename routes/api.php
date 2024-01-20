<?php

use App\Http\Controllers\AuctionHuiDetailController;
use App\Http\Controllers\AuctionHuiRoomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankAccountController;
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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




Route::post('login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'signup']);

Route::get('images/users/{filename}', [ImageController::class, 'getImageUser'])->name('images.get');
Route::get('images/products/{filename}', [ImageController::class, 'getImageProduct'])->name('images.get');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('/update-avatar/{id}', [UserController::class, 'updateAvatar']);
    Route::post('/update-password/{user}', [UserController::class, 'updatePassword']);
    Route::post('/update-cccd/{id}', [UserController::class, 'updateCCCD']);
    Route::post('/update-info/{id}', [UserController::class, 'updateInfo']);
    Route::get('users', [UserController::class, 'getAllUser']);
    Route::post('user/update-rank/{id}', [UserController::class, 'updateRank']);


    Route::get('room/user/{userId}/{item}', [RoomController::class, 'getRoomsByUserId']);
    Route::get('rooms/{item}', [RoomController::class, 'getAllRoomsWithUsers']);
    Route::post('room/actionroom', [RoomUserController::class, 'addUserForRoom']);
    Route::get('look-user-in-room/{id}', [RoomUserController::class, 'updateStatusUser']);
    Route::post('room/addroom', [RoomController::class, 'addroom']);
    Route::get('room/detail/{id}', [RoomController::class, 'getDetailRoom']);
    Route::get('rooms/count/{id}', [RoomController::class, 'getRoomsByCount']);
    Route::get('rooms/price/{id}', [RoomController::class, 'getRoomsByPrice']);
    Route::post('room/update-status/', [RoomController::class, 'updateStatusRoom']);
    Route::post('room/update-info/{id}', [RoomController::class, 'updateInfoRoom']);

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
    Route::get('auction/user-win/{id}', [UserWinHuiController::class, 'getHuiByUser']);
    Route::get('auction/{id}', [UserWinHuiController::class, 'updatePaid']);
    Route::get('auction-user-win', [UserWinHuiController::class, 'getAll']);
    Route::get('user/total-price/{id}', [UserWinHuiController::class, 'calculateTotalAmountsByRoom']);

    Route::get('checkout', [CheckoutController::class, 'getAll']);
    Route::post('checkout', [CheckoutController::class, 'checkout']);
    Route::get('checkout/user/{id}', [CheckoutController::class, 'getByUser']);
    Route::post('create-payment-link', [CheckoutController::class, 'createPaymentLink']);
    Route::post('create-payment-room-link', [CheckoutController::class, 'createPaymenRoomUsertLink']);
    Route::get('payment-info/{id}', [CheckoutController::class, 'getPaymentLinkInfoOfOrder']);
    Route::get('payment-cancel/{id}', [CheckoutController::class, 'cancelPaymentLinkOfOrder']);

    Route::get('bank/user/{id}', [BankAccountController::class, 'getBankByUser']);
    Route::post('addbank', [BankAccountController::class, 'addBank']);
});
