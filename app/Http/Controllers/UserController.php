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
                return $this->errorResponse("Validation error", 400);
            }

            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User not found',  404);
            }

            $cccdAfterPath = $image->uploadImage($request->file('cccd_after'), 'images/users');
            $cccdBeforePath = $image->uploadImage($request->file('cccd_before'), 'images/users');

            $user->update([
                'cccd_after' => $cccdAfterPath,
                'cccd_before' => $cccdBeforePath,
            ]);

            return $this->successResponse("User update CCCD Successfully", new UserResource($user), 201);
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
                return $this->errorResponse('Validation error',  400);
            }

            $data =  $user->password = Hash::make($request->password);
            $user->update([
                'password' => $data
            ]);

            return $this->successResponse('Password updated successfully', null, 200);
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
                return $this->errorResponse('Input error value',  400);
            }

            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User not found',  404);
            }

            $data = $request->all();
            $user->update($data);
            return $this->successResponse('User update successfully', new UserResource($user), 201);
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
                return $this->errorResponse('Input error value',  400);
            }
            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User not found',  404);
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
            return $this->successResponse('User update avatar successfully', new UserResource($user), 201);
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
