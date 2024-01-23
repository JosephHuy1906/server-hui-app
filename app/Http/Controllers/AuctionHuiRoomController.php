<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuctionHuiRoomResource;
use App\Models\AuctionHuiDetail;
use App\Models\AuctionHuiRoom;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AuctionHuiRoomController extends Controller
{
    public function createAuctionHui(Request $request)
    {
        try {

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
            $roomAuction = AuctionHuiRoom::where('room_id', $request->room_id)->first();
            if (!$room) {
                return $this->errorResponse('Phòng hụi không tồn tại',  404);
            }
            if ($roomAuction) {
                return $this->errorResponse('Phòng đấu hụi đang tồn tại bạn không thể tạo thêm phòng được',  400);
            }

            $auction = AuctionHuiRoom::create($request->all());
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
