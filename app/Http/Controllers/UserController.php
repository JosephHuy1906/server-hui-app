<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function updateCCCD(Request $request, $id)
    {
        try {
            $image = new ImageController();
            $validateUser = Validator::make(
                $request->all(),
                [
                    'cccd_after' => 'required|image|mimes:jpeg,png,jpg|max:4048',
                    'cccd_before' => 'required|image|mimes:jpeg,png,jpg|max:4048',
                ]
            );

            if ($validateUser->fails()) {
                return $this->errorResponse("Thông tin truyền vào chưa đúng", 400);
            }

            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User không tồn tại',  404);
            }

            $cccdAfterPath = $image->uploadImage($request->file('cccd_after'), 'images/users');
            $cccdBeforePath = $image->uploadImage($request->file('cccd_before'), 'images/users');

            $user->update([
                'cccd_after' => $cccdAfterPath,
                'cccd_before' => $cccdBeforePath,
            ]);

            return $this->successResponse("Cập nhập CCCD hoặc CMDD thành công", new UserResource($user), 201);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    public function updateRank(Request $request, $id)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'rank' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                return $this->errorResponse("Thông tin truyền vào chưa đúng", 400);
            }

            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User không tồn tại',  404);
            }


            $user->update([
                'rank' => $request->rank,
            ]);

            return $this->successResponse("Cập nhập hạng thành viên thành công", new UserResource($user), 201);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    public function updatePassword(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }

            $data =  $user->password = Hash::make($request->password);
            $user->update([
                'password' => $data
            ]);

            return $this->successResponse('Cập nhập lại password thành công', null, 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Server Error',  500);
        }
    }

    public function updateInfo(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes',
                'email' => 'sometimes|email',
                'phone' => 'sometimes',
                'address' => 'sometimes',
                'birthday' => 'sometimes',
                'sex' => 'sometimes',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }

            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User không tồn tại',  404);
            }

            $data = $request->all();
            $user->update($data);
            return $this->successResponse('Cập nhập thông tin thành công', new UserResource($user), 201);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    public function updateAvatar(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:png,jpg,jpeg,gif|max:2048',
            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }
            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User Không tồn tại',  404);
            }
            $oldAvatarPath = str_replace(url('/') . '/api/', '', $user->avatar);
            $avatar = $request->file('avatar');
            $storagePath = 'images/users';
            $filename = time() . '_' . $avatar->getClientOriginalName();
            $avatar->storeAs($storagePath, $filename, 'public');
            if ($oldAvatarPath && Storage::disk('public')->exists($oldAvatarPath)) {
                Storage::disk('public')->delete($oldAvatarPath);
            }
            $baseUrl = Config::get('app.url');
            $user->update([
                'avatar' => $baseUrl . '/api/' . $storagePath . '/' . $filename,
            ]);
            return $this->successResponse('Cập nhập avatar thành công', new UserResource($user), 201);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    public function getAllUser()
    {
        try {
            $data = User::all();
            return $this->successResponse('Get all user successfully',  UserResource::collection($data), 201);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function getUserByID($id)
    {
        try {
            $data = User::find($id);
            if (!$data) {
                return $this->errorResponse('User id sai hoặc không tồn tại', 401);
            }
            return $this->successResponse('Get all user successfully',  UserResource::collection($data), 201);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    public function checkMailAndSeid(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validate->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->errorResponse('Email bạn chưa đăng ký tài khoản',  400);
            }

            $name = $user->name;
            $code =  Str::random(64);
            $user->update([
                "code" => $code
            ]);
            Mail::send('emails.forgetPass', compact('name', 'code'), function ($email) use ($request) {
                $email->to($request->email, 'putapp')
                    ->subject('Khôi phục lại password');
            });
            return $this->successResponse('Email khôi phục mật khẩu đã được gửi', [], 200);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(),  500);
        }
    }
    public function forgetPassword(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password',
                'code' => 'required'
            ]);

            if ($validate->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng',  400);
            }
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->errorResponse('Email bạn chưa đăng ký tài khoản',  404);
            }
            if ($user->code !== $request->code) {
                return $this->errorResponse('Code bạn nhập chưa đúng',  401);
            }

            $pass =  $user->password = Hash::make($request->password);
            $user->update([
                'password' => $pass,
                'code' => ""
            ]);
            return $this->successResponse('Bạn đã đổi lại password thành công', null, 201);
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(),  500);
        }
    }
}
