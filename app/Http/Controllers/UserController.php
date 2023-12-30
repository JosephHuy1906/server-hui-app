<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function createUser(Request $request)
    {
        try {
            $response = new ResponseController();
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:6',
                    'phone' => 'required',
                ]
            );
            $image = new ImageController();
            if ($validateUser->fails()) {
                return $response->errorResponse("Validation error", $validateUser->errors(), 400);
            }
            $email = User::find($request->email);
            if ($email) {
                return $response->errorResponse("Email is already exist", null, 404);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => 'required',
            ]);

            return $response->successResponse("User Created Successfully", new UserResource($user), 200);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
    public function updateCCCD(Request $request, $id)
    {
        try {
            $image = new ImageController();
            $response = new ResponseController();
            $validateUser = Validator::make(
                $request->all(),
                [
                    'cccd_after' => 'required|image|mimes:jpeg,png,jpg|max:4048',
                    'cccd_before' => 'required|image|mimes:jpeg,png,jpg|max:4048',
                ]
            );

            if ($validateUser->fails()) {
                return $response->errorResponse("Validation error", $validateUser->errors(), 400);
            }

            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User not found', null, 404);
            }

            $cccdAfterPath = $image->uploadImage($request->file('cccd_after'), 'images/users');
            $cccdBeforePath = $image->uploadImage($request->file('cccd_before'), 'images/users');

            $user->update([
                'cccd_after' => $cccdAfterPath,
                'cccd_before' => $cccdBeforePath,
            ]);

            return $response->successResponse("User update CCCD Successfully", new UserResource($user), 201);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function updatePassword(Request $request, User $user)
    {
        try {
            $response = new ResponseController();
            if ($user->id !== auth()->id()) {
                return $response->errorResponse('Unauthorized', 'You do not have permission to update this password', 401);
            }

            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password',
            ]);

            if ($validator->fails()) {
                return $response->errorResponse('Validation error', $validator->errors(), 400);
            }

            $data =  $user->password = Hash::make($request->password);
            $user->update([
                'password' => $data
            ]);

            return $response->successResponse('Password updated successfully', null, 200);
        } catch (\Throwable $th) {
            return $response->errorResponse('Server Error', $th->getMessage(), 500);
        }
    }

    public function updateInfo(Request $request, $id)
    {
        try {
            $response = new ResponseController();
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required',
                'email' => 'sometimes|required|email',
                'phone' => 'sometimes|required',
                'address' => 'sometimes|required',
                'birthday' => 'sometimes|required',
                'sex' => 'sometimes|required',
            ]);

            if ($validator->fails()) {
                return $response->errorResponse('Input error value', $validator->errors(), 400);
            }

            $user = User::find($id);
            if (!$user) {
                return $this->errorResponse('User not found', null, 404);
            }

            $data = $request->all();
            $user->update($data);
            return $response->successResponse('User update successfully', new UserResource($user), 201);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function updateAvatar(Request $request, $id)
    {
        try {
            $response = new ResponseController();
            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:png,jpg,jpeg,gif|max:2048',
            ]);
            if ($validator->fails()) {
                return $response->errorResponse('Input error value', $validator->errors(), 400);
            }
            $user = User::find($id);
            if (!$user) {
                return $response->errorResponse('User not found', null, 404);
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
            return $response->successResponse('User update avatar successfully', new UserResource($user), 201);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function getAllUser()
    {
        try {
            $response = new ResponseController();
            $data = User::all();
            return $response->successResponse('Get all user successfully',  UserResource::collection($data), 201);
        } catch (\Throwable $th) {
            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
}
