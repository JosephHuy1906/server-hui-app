<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuctionHuiDetailResource;
use App\Http\Resources\UserWinHuiResource;
use App\Models\AuctionHuiDetail;
use App\Models\AuctionHuiRoom;
use App\Models\Room;
use App\Models\User;
use App\Models\UserWinHui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuctionHuiDetailController extends Controller
{
    public function auctionHui(Request $request)
    {
        try {

            $noti = new NotificationController();
            $validator = Validator::make($request->all(), [
                'auction_hui_id' => 'required',
                'user_id' => 'required',
                'starting_price' => 'required',
                'auction_percentage' => 'required',
                'total_price' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }
            $auction = AuctionHuiRoom::find($request->auction_hui_id);
            $price_end = $this->checkPriceAuction($request->auction_hui_id);
            if ($price_end && $request->total_price <= $price_end->total_price) {
                return $this->errorResponse('Số tiền đấu giá phải cao hơn người đã đấu trước bạn', 401);
            }
            $create = AuctionHuiDetail::create($request->all());
            $auction->update([
                "auction_price" => $request->total_price
            ]);
            $noti->postNotification(
                $request->user_id,
                'user',
                'Bạn đã đấu giá hụi thành công',
                $request->room_id,
                "payment_auction"
            );
            return $this->successResponse('Bạn đã đấu giá hụi thành công', new AuctionHuiDetailResource($create), 201);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(),  500);
        }
    }
    public function getAuctionHui($id)
    {
        try {

            $data = AuctionHuiDetail::where('auction_hui_id', $id)->get();
            return $this->successResponse('Lấy danh sách đấu giá hụi theo phòng thành công', AuctionHuiDetailResource::collection($data), 200);
        } catch (\Throwable $err) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    public function getTotal($id)
    {
        try {
            $maxTotalPrice = AuctionHuiDetail::where('auction_hui_id', $id)
                ->select(DB::raw('MAX(total_price) as max_total_price'))
                ->first()->max_total_price;

            $usersWithMaxTotalPrice = AuctionHuiDetail::where('auction_hui_id', $id)
                ->where('total_price', $maxTotalPrice)->get();

            return $this->successResponse('Người đấu giá hụi thành công', AuctionHuiDetailResource::collection($usersWithMaxTotalPrice), 200);
        } catch (\Throwable $err) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function checkPriceAuction($id)
    {
        try {
            $maxTotalPrice = AuctionHuiDetail::where('auction_hui_id', $id)
                ->select(DB::raw('MAX(total_price) as max_total_price'))
                ->first()->max_total_price;

            $usersWithMaxTotalPrice = AuctionHuiDetail::where('auction_hui_id', $id)
                ->where('total_price', $maxTotalPrice)->first();
            return $usersWithMaxTotalPrice;
        } catch (\Throwable $err) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function postUserWin(Request $request)
    {
        try {
            $auction = new AuctionHuiRoomController();
            $notication = new NotificationController();
            $validator = Validator::make($request->all(), [
                'room_id' => 'required',
                'auction_hui_id' => 'required',
                'accumulated_amount' => 'required',
                'commission_percentage' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }

            $usersWithMaxTotalPrice = $this->getTotal($request->auction_hui_id);
            if (!$usersWithMaxTotalPrice) {
                $auction->removeAuctionHui($request->auction_hui_id);
                return $this->successResponse('Phòng đấu hụi đã kết thúc', null, 201);
            }

            $responseData = $usersWithMaxTotalPrice->getData();
            if (
                !isset($responseData->status) ||
                !isset($responseData->success) ||
                $responseData->status != 200 ||
                !$responseData->success
            ) {
                return $this->errorResponse('Không tìm được người chiến thắng', 500);
            }
            $winningBidder = $responseData->data[0];
            $total_amount_payable = $winningBidder->total_price;
            $total_money_received = $request->accumulated_amount - (($request->accumulated_amount * $request->commission_percentage) / 100);
            $user = User::find($winningBidder->user->user_id);
            $addUserWin = [
                'user_id' => $winningBidder->user->user_id,
                'commission_percentage' => $request->commission_percentage,
                'price_pay_hui' => $winningBidder->total_price,
                'total_auction' => $request->accumulated_amount,
                'room_id' => $request->room_id,
                'total_amount_payable' => $total_amount_payable,
                'total_money_received' => $total_money_received,
            ];

            $addUser =  UserWinHui::create($addUserWin);
            $totalAmountPayable = number_format($total_money_received, 0, ',', '.');
            $notication->postNotification(
                $winningBidder->user->user_id,
                'User',
                'Bạn đã đấu hụi thành công với số tiền: ' . $totalAmountPayable . 'đ.Vui lòng thanh toán để nhận số tiền trên',
                $request->room_id,
                "payment_auction"
            );
            $this->sendNoticationApp(
                $user->device_id,
                'Bạn đã đấu hụi thành công với số tiền: ' . $totalAmountPayable . 'đ.Vui lòng thanh toán để nhận số tiền trên',
            );
            return $this->successResponse('Create user win hui successfully', new UserWinHuiResource($addUser), 201);
        } catch (\Throwable $err) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function postUserWinServer($room_id, $auction_hui_id, $accumulated_amount, $commission_percentage)
    {
        try {
            $auction = new AuctionHuiRoomController();
            $notication = new NotificationController();
            $usersWithMaxTotalPrice = $this->getTotal($auction_hui_id);

            $responseData = $usersWithMaxTotalPrice->getData();

            $winningBidder = $responseData->data[0];
            $total_amount_payable = $winningBidder->total_price;
            $total_money_received = $accumulated_amount - (($accumulated_amount * $commission_percentage) / 100);
            $user = User::find($winningBidder->user->user_id);
            $room = Room::find($room_id);
            if (!$usersWithMaxTotalPrice) {
                $auction->removeAuctionHui($auction_hui_id);
                $notication->postNotification(
                    $winningBidder->user->user_id,
                    'User',
                    'Phòng đấu giá hụi ' . $room->title . ' đã kết thúc. Vì không có ai đấu giá nên tiền tích luỹ sẽ giữ nguyên',
                    $room_id,
                    "room_all"
                );
                $this->sendNoticationApp(
                    $user->device_id,
                    'Phòng đấu giá hụi ' . $room->title . ' đã kết thúc. Vì không có ai đấu giá nên tiền tích luỹ sẽ giữ nguyên',
                );
                return;
            }
            if (
                !isset($responseData->status) ||
                !isset($responseData->success) ||
                $responseData->status != 200 ||
                !$responseData->success
            ) {
                $auction->removeAuctionHui($auction_hui_id);
                $notication->postNotification(
                    $winningBidder->user->user_id,
                    'User',
                    'Phòng đấu giá hụi ' . $room->title . ' đã kết thúc. Vì không có ai đấu giá nên tiền tích luỹ sẽ giữ nguyên',
                    $room_id,
                    "room_all"
                );
                $this->sendNoticationApp(
                    $user->device_id,
                    'Phòng đấu giá hụi ' . $room->title . ' đã kết thúc. Vì không có ai đấu giá nên tiền tích luỹ sẽ giữ nguyên',
                );
                return;
            }

            $addUserWin = [
                'user_id' => $winningBidder->user->user_id,
                'commission_percentage' => $commission_percentage,
                'price_pay_hui' => $winningBidder->total_price,
                'total_auction' => $accumulated_amount,
                'room_id' => $room_id,
                'total_amount_payable' => $total_amount_payable,
                'total_money_received' => $total_money_received,
            ];

            UserWinHui::create($addUserWin);
            $totalAmountPayable = number_format($total_money_received, 0, ',', '.');
            $notication->postNotification(
                $winningBidder->user->user_id,
                'User',
                'Bạn đã đấu hụi thành công với số tiền: ' . $totalAmountPayable . 'đ.Vui lòng thanh toán để nhận số tiền trên',
                $room_id,
                "payment_auction"
            );
            $this->sendNoticationApp(
                $user->device_id,
                'Bạn đã đấu hụi thành công với số tiền: ' . $totalAmountPayable . 'đ.Vui lòng thanh toán để nhận số tiền trên',
            );
            $auction->removeAuctionHui($auction_hui_id);
            return;
        } catch (\Throwable $err) {
            return $this->errorResponse("Server Error",  500);
        }
    }
}
