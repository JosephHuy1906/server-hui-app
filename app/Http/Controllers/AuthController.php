<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'signup']]);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'status' => 401,
                'message' => 'Email or password incorrect'
            ], 401);
        }

        $refreshToken = $this->createRefreshToken();

        return $this->respondWithToken($token, $refreshToken);
    }
    public function signup(Request $request)
    {
        try {

            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:6',
                    'cccd_after' => 'required|image|mimes:jpeg,png,jpg|max:4048',
                    'cccd_before' => 'required|image|mimes:jpeg,png,jpg|max:4048',
                ]
            );
            $response = new ResponseController();
            $image = new ImageController();
            if ($validateUser->fails()) {
                return $response->errorResponse("Validation error", $validateUser->errors(), 400);
            }
            $email = User::find($request->email);
            if ($email) {
                return $response->errorResponse("Email is already exist", null, 404);
            }

            $cccdAfterPath = $image->uploadImage($request->file('cccd_after'), 'images/users');
            $cccdBeforePath = $image->uploadImage($request->file('cccd_before'), 'images/users');

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'cccd_after' => $cccdAfterPath,
                'cccd_before' => $cccdBeforePath,
            ]);

            return $response->successResponse("User Created Successfully", new UserResource($user), 200);
        } catch (\Throwable $th) {

            return $response->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function profile()
    {
        try {
            return response()->json([
                'success' => true,
                'status' => 200,
                'message' => 'get user successfuly',
                'data' => auth('api')->user()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Token Unauthorized'
            ], 500);
        }
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Successfully logged out'
        ], 200);
    }
    public function refresh()
    {
        $refreshToken  = request()->refresh_token;
        try {

            $decoded = JWTAuth::getJWTProvider()->decode($refreshToken);
            $user = User::find($decoded['user_id']);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'status' => 404,
                    'message' => 'User not found'
                ], 404);
            }

            $token = auth()->login($user);
            $refreshToken = $this->createRefreshToken();

            return  $this->respondWithToken($token, $refreshToken);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Refresh token in Invald'
            ], 500);
        }
    }
    private function respondWithToken($token, $refreshToken)
    {
        return response()->json([
            'message' => 'Login successfully',
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 180
        ]);
    }
    private function createRefreshToken()
    {
        $data = [
            'user_id' => auth()->user()->id,
            'ramdo' => rand() . time(),
            'exp' => time() + config('jwt.refresh_ttl')
        ];
        $refreshToken = JWTAuth::getJWTProvider()->encode($data);
        return $refreshToken;
    }
}
