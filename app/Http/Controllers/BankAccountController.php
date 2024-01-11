<?php

namespace App\Http\Controllers;

use App\Http\Resources\BankAccounResource;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
{
    public function addBank(Request $request)
    {
        try {
            $response = new ResponseController();
            $validate = Validator::make(
                $request->all(),
                [
                    'short_name' => 'required',
                    'number_bank' => 'required',
                    'code' => 'required',
                    'user_id' => 'required',
                ]
            );
            if ($validate->fails()) {
                return $response->errorResponse('Bạn chưa điền đủ thông tin', $validate->errors(), 400);
            }
            $user = User::find($request->user_id);
            if (!$user) {
                return $response->errorResponse('Tài khoản người dùng không đúng', null, 404);
            }
            $addBank = BankAccount::create($request->all());
            return $response->successResponse("Thêm tài khoản ngân hàng thành công", BankAccounResource::collection($addBank), 201);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function getBankByUser($userId)
    {
        try {
            $response = new ResponseController();
            $user = User::find($userId);
            if (!$user) {
                return $response->errorResponse('Tài khoản người dùng không đúng', null, 404);
            }
            $data = BankAccount::where('user_id', $userId)->get();
            return $response->successResponse("Lấy bank theo user thành công", BankAccounResource::collection($data), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
}
