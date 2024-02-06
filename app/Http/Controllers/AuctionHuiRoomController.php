<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuctionHuiRoomResource;
use App\Models\AuctionHuiDetail;
use App\Models\AuctionHuiRoom;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AuctionHuiRoomController extends Controller
{
    public function createAuctionHui(Request $request)
    {
        try {
            $notication = new NotificationController();
            $valida = Validator::make(
                $request->all(),
                [
                    'starting_price' => 'required',
                    'time_end' => 'required',
                    'room_id' => 'required'
                ]
            );

            if ($valida->fails()) {
                return $this->errorResponse("Thông tin truyền vào chưa đúng",  400);
            }
            $room = Room::find($request->room_id);
            $room_user = RoomUser::where('room_id', $request->room_id)
                ->where('status', 'Đang hoạt động')->get();
            $roomAuction = AuctionHuiRoom::where('room_id', $request->room_id)->first();
            if (!$room) {
                return $this->errorResponse('Phòng hụi không tồn tại',  404);
            }
            if ($roomAuction) {
                return $this->errorResponse('Phòng đấu hụi đang tồn tại bạn không thể tạo thêm phòng được',  400);
            }

            $auction = AuctionHuiRoom::create($request->all());
            foreach ($room_user as $us) {
                $user = User::find($us->user_id);
                $this->sendNoticationApp(
                    $user->device_id,
                    'Phòng ' . $room->title . ' đã bắt đầu đấu giá hụi vui lòng vào app để đấu giá. Thời gian kết thúc đấu giá vào lúc ' . $request->time_end
                );
                $notication->postNotification(
                    $user->id,
                    'User',
                    'Phòng Hụi ' . $room->title . '  đã bắt đầu đấu giá hụi vui lòng vào app để đấu giá. Thời gian kết thúc đấu giá vào lúc ' . $request->time_end,
                    $room->id,
                    "room_all"
                );
            }
            return $this->successResponse("Phòng đấu giá hui đã tạo", new AuctionHuiRoomResource($auction), 201);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(),  500);
        }
    }


    public function removeAuctionHui($id)
    {
        try {

            AuctionHuiDetail::where('auction_hui_id', $id)->delete();
            AuctionHuiRoom::where('id', $id)->delete();

            return $this->successResponse("Xoá phòng đấu giá hui thành công", null, 201);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
}
