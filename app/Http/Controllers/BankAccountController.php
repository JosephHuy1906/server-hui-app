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

            $validate = Validator::make(
                $request->all(),
                [
                    'short_name' => 'required',
                    'number_bank' => 'required',
                    'code' => 'required',
                    'user_id' => 'required',
                    'logo' => 'required',
                    'name' => 'required',
                ]
            );
            if ($validate->fails()) {
                return $this->errorResponse('Bạn chưa điền đủ thông tin',  400);
            }
            $user = User::find($request->user_id);
            if (!$user) {
                return $this->errorResponse('Tài khoản người dùng không đúng', 404);
            }
            $addBank = BankAccount::create($request->all());
            return $this->successResponse("Thêm tài khoản ngân hàng thành công", new BankAccounResource($addBank), 201);
        } catch (\Throwable $th) {
            return $this->errorResponse("error server",  500);
        }
    }

    public function getBankByUser($userId)
    {
        try {

            $user = User::find($userId);
            if (!$user) {
                return $this->errorResponse('Tài khoản người dùng không đúng',  404);
            }
            $data = BankAccount::where('user_id', $userId)->get();
            return $this->successResponse("Lấy bank theo user thành công", BankAccounResource::collection($data), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
}
