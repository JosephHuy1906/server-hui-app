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
            'device_id' => 'sometimes',
        ]);
        if ($validateUser->fails()) {
            return $this->errorResponse("Vui lòng điền đúng và đủ thông tin",  404);
        }

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return $this->errorResponse("Email hoặc password không đúng",  401);
        }
        $user = User::where('email', $request->email)->first();
        if ($request->device_id) {
            $user->update([
                "device_id" => $request->device_id,
            ]);
        }
        $data = [
            'user' => $user,
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ];
        return $this->successResponse('Đăng nhập thành công', $data, 200);
    }
    public function signup(Request $request)
    {
        try {
            $image = new ImageController();
            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'phone' => 'required',
                'cccd_before' => 'sometimes|image|mimes:jpeg,png,jpg|max:4048',
                'cccd_after' => 'sometimes|image|mimes:jpeg,png,jpg|max:4048',
            ]);
            if ($validate->fails()) {
                return $this->errorResponse("Vui lòng điền đúng và đủ thông tin",  404);
            }
            $check = User::where('email', $request->email)->first();
            if ($check) {
                return $this->errorResponse("Email này đã được đăng ký",  401);
            }
            if ($request->file('cccd_after') && $request->file('cccd_before')) {
                $cccdAfterPath = $image->uploadImage($request->file('cccd_after'), 'images/users');
                $cccdBeforePath = $image->uploadImage($request->file('cccd_before'), 'images/users');

                $user =  User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'cccd_after' => $cccdAfterPath,
                    'cccd_before' => $cccdBeforePath,
                ]);

                $data = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'cccd_after' => $cccdAfterPath,
                    'cccd_before' => $cccdBeforePath,
                    'token' => $user->createToken("API TOKEN")->plainTextToken
                ];
                return $this->successResponse("Đăng ký tài khoản thành công", $data, 201);
            } else {
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
            }
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
