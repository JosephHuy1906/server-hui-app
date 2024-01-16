<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function login(Request $request)
    {

        $validateUser = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validateUser->fails()) {
            return $this->errorResponse("Vui lòng điền đúng và đủ thông tin",  401);
        }

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return $this->errorResponse("Email hoặc password không đúng",  401);
        }
        $user = User::where('email', $request->email)->first();

        $data = [
            'user' => $user,
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ];
        return $this->successResponse('Đăng nhập thành công', $data, 200);
    }
    public function signup(Request $request)
    {
        try {

            $validate = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:6',
                    'phone' => 'required',
                ]
            );
            if ($validate->fails()) {
                return $this->errorResponse("Vui lòng điền đủ thông tin",  401);
            }
            $email = User::find($request->email);
            if ($email) {
                return $this->errorResponse("Email Không tồn tại", 404);
            }
            $user =  User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ];
            return $this->successResponse("Đăng ký tài khoản thành công", $data, 201);
        } catch (\Throwable $th) {
            return $this->error("Server Error", 500);
        }
    }



    public function logout()
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens()->delete();
            return $this->successResponse('Logout successful', null, 200);
        }

        return $this->errorResponse('User not found', 404);
    }
}
