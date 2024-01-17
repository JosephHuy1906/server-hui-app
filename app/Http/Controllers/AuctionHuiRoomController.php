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
            if (!$room) {
                return $this->errorResponse('room not found',  404);
            }

            $auction = AuctionHuiRoom::create($request->all());
            return $this->successResponse("Phòng đấu giá hui đã tạo", new AuctionHuiRoomResource($auction), 201);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
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
