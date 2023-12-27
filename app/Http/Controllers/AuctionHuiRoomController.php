<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuctionHuiRoomResource;
use App\Models\AuctionHuiDetail;
use App\Models\AuctionHuiRoom;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuctionHuiRoomController extends Controller
{
    public function createAuctionHui(Request $request)
    {
        try {
            $response = new ResponseController();
            $valida = Validator::make(
                $request->all(),
                [
                    'starting_price' => 'required',
                    'time_end' => 'required',
                    'room_id' => 'required'
                ]
            );
            if ($valida->fails()) {
                return $response->errorResponse("Input does not exist", null, 400);
            }
            $room = Room::find($request->room_id);
            if (!$room) {
                return $this->errorResponse('room not found', null, 404);
            }
            $auction = AuctionHuiRoom::create($request->all());
            return $response->successResponse("Phòng đấu giá hui đã tạo", new AuctionHuiRoomResource($auction), 201);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function removeAuctionHui($id)
    {
        try {
            $response = new ResponseController();
            AuctionHuiDetail::where('auction_hui_id', $id)->delete();
            AuctionHuiRoom::where('id', $id)->delete();

            return $response->successResponse("Xoá phòng đấu giá hui thành công", null, 201);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
}
