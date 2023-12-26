<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{

    public function getAllRoomsWithUsers($item)
    {
        try {
            $response = new ResponseController();
            $rooms = Room::withCount('users')->with(['users'])->take($item)->get();
            return $response->successResponse('Get room all success', RoomResource::collection($rooms), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function getRoomsByUserId($userId, $item)
    {
        try {
            $response = new ResponseController();
            $userRooms = Room::whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->withCount('users')->with(['users'])->take($item)->get();

            return $response->successResponse('Get room by User success', RoomResource::collection($userRooms), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function addroom(Request $request)
    {
        try {
            $response = new ResponseController();
            $validate = Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'price_room' => 'required',
                    'commission_percentage' => 'required',
                    'date_end' => 'required',
                    'date_start' => 'required',
                    'avatar' => 'sometimes|required|image|mimes:jpeg,png,jpg|max:4048',
                ]
            );

            if ($validate->fails()) {
                return $response->errorResponse('Input error value', $validate->errors(), 400);
            }

            $addroom = Room::create($request->all());
            return $response->successResponse('Create room success', new RoomResource($addroom), 201);
        } catch (\Throwable $err) {
            return $response->errorResponse('Create room faill', $err->getMessage(), 500);
        }
    }
}
