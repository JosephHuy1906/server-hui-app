<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
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
            if ($user->id !== auth()->id()) {
                return $this->errorResponse('Unauthorized',  401);
            }

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
                'name' => 'sometimes|required',
                'email' => 'sometimes|required|email',
                'phone' => 'sometimes|required',
                'address' => 'sometimes|required',
                'birthday' => 'sometimes|required',
                'sex' => 'sometimes|required',
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
}
