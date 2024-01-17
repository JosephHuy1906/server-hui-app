<?php

namespace App\Http\Controllers;

use App\Http\Resources\CheckoutResource;
use App\Models\Checkout;
use App\Models\User;
use App\Models\UserWinHui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function updateStatus($id)
    {
        try {

            $checkout = Checkout::find($id);
            $notication = new NotificationController();
            $checkout->update([
                'status' => 'approved'
            ]);
            $find = UserWinHui::find($checkout->user_win_hui_id);
            if ($find) {
                $find->update([
                    'status' => 'approved'
                ]);
                $totalAmountPayable = number_format($find->total_amount_payable, 0, ',', '.');
                $notication->postNotification($find->user_id, 'user', 'Bạn đã thanh toán ' . $totalAmountPayable . 'đ', $find->room_id);
                $notication->postNotification($find->user_id, 'admin', 'User ' . $find->user_id . ' đã thanh toán tiền' . $totalAmountPayable . 'đ', $find->room_id);
            }
            if (!$find) {
                $totalAmountPayable = number_format($checkout->price, 0, ',', '.');
                $notication->postNotification(
                    $checkout->user_id,
                    'user',
                    'Bạn đã thanh toán ' . $totalAmountPayable . 'đ',
                    null
                );
                $notication->postNotification(
                    $checkout->user_id,
                    'admin',
                    'User ' . $checkout->user_id . ' đã thanh toán tiền' . $totalAmountPayable . 'đ',
                    null
                );
            }
        } catch (\Throwable $e) {
            return $this->errorResponse('Server Error',  500);
        }
    }
    public function updateStatusReject($id)
    {
        try {

            $checkout = Checkout::find($id);
            $notication = new NotificationController();
            $checkout->update([
                'status' => 'rejected'
            ]);
            $find = UserWinHui::find($checkout->user_win_hui_id);
            if ($find) {
                $find->update([
                    'status' => 'rejected'
                ]);
                $totalAmountPayable = number_format($checkout->price, 0, ',', '.');
                $notication->postNotification(
                    $checkout->user_id,
                    'User',
                    'Bạn đã huỷ hoá đơn ' . $totalAmountPayable . 'đ ',
                    $find->room_id
                );
                $notication->postNotification(
                    $checkout->user_id,
                    'Admin',
                    'User ' . $checkout->user_id . ' đã huỷ hoá đơn' . $totalAmountPayable . 'đ ',
                    $find->room_id
                );
            }
            if (!$find) {
                $totalAmountPayable = number_format($checkout->price, 0, ',', '.');
                $notication->postNotification(
                    $checkout->user_id,
                    'User',
                    'Bạn đã huỷ hoá đơn ' . $totalAmountPayable . 'đ ',
                    null
                );
                $notication->postNotification(
                    $checkout->user_id,
                    'Admin',
                    'User ' . $checkout->user_id . ' đã huỷ hoá đơn' . $totalAmountPayable . 'đ ',
                    null
                );
            }
        } catch (\Throwable $e) {
            return $this->errorResponse('Server Error',  500);
        }
    }

    public function createPaymentLink(Request $request)
    {
        try {

            $notication = new NotificationController();
            $baseUrl = env('APP_URL');
            $validator = Validator::make($request->all(), [
                'amount' => 'required',
                'description' => 'required',
                'user_id' => 'required',
                'room_id' => 'required',
                'user_win_hui_id' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng', 400);
            }
            $oderID = intval(substr(strval(microtime(true) * 10000), -64));
            $data = [
                "orderCode" => $oderID,
                "amount" => $request->amount,
                "description" => $request->description,
                "returnUrl" => $baseUrl . "/success",
                "cancelUrl" => $baseUrl . "/cancel"
            ];
            $PAYOS_CLIENT_ID = env('PAYOS_CLIENT_ID');
            $PAYOS_API_KEY = env('PAYOS_API_KEY');
            $PAYOS_CHECKSUM_KEY = env('PAYOS_CHECKSUM_KEY');
            $requestSignature = $this->createSignaturePaymentRequest($PAYOS_CHECKSUM_KEY, $data);
            $data["signature"] = $requestSignature;
            $response = Http::withHeaders([
                "x-client-id" => $PAYOS_CLIENT_ID,
                "x-api-key" => $PAYOS_API_KEY
            ])->post("https://api-merchant.payos.vn/v2/payment-requests", $data)->json();
            $responseDataSignature = $this->createSignatureFromObj($PAYOS_CHECKSUM_KEY, $response["data"]);
            if ($responseDataSignature != $response["signature"]) {
                return $this->errorResponse("Signature not match",  404);
            }
            $dataCheckOut = [
                "id" => $oderID,
                "price" => $request->amount,
                "description" => $request->description,
                "user_id" => $request->user_id,
                'room_id' => $request->room_id,
                'user_win_hui_id' => $request->user_win_hui_id
            ];
            Checkout::create($dataCheckOut);
            $totalAmountPayable = number_format($request->amount, 0, ',', '.');
            $notication->postNotification(
                $request->user_id,
                'User',
                'Hoá đơn với số tiền: ' . $totalAmountPayable . 'đ đang chờ bạn thanh toán',
                $request->room_id
            );
            return $this->successResponse(
                'Create Payment link success',
                ['bankURL' => $response["data"]["checkoutUrl"]],
                201
            );
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    public function getPaymentLinkInfoOfOrder($id)
    {
        try {

            $PAYOS_CLIENT_ID = env('PAYOS_CLIENT_ID');
            $PAYOS_API_KEY = env('PAYOS_API_KEY');
            $PAYOS_CHECKSUM_KEY = env('PAYOS_CHECKSUM_KEY');

            $response = Http::withHeaders([
                "x-client-id" => $PAYOS_CLIENT_ID,
                "x-api-key" => $PAYOS_API_KEY
            ])->get("https://api-merchant.payos.vn/v2/payment-requests/{$id}")->json();

            $responseDataSignature = $this->createSignatureFromObj($PAYOS_CHECKSUM_KEY, $response["data"]);
            if ($responseDataSignature != $response["signature"]) {
                return $this->errorResponse("Chữ ký không khớp",  400);
            }
            return $this->successResponse("Get Payment link info success", $response["data"], 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function cancelPaymentLinkOfOrder(Request $request, String $id)
    {
        try {
            $notication = new NotificationController();

            $body = json_decode($request->getContent(), true);
            $PAYOS_CLIENT_ID = env('PAYOS_CLIENT_ID');
            $PAYOS_API_KEY = env('PAYOS_API_KEY');
            $PAYOS_CHECKSUM_KEY = env('PAYOS_CHECKSUM_KEY');


            $cancelBody = is_array($body) && $body["cancellationReason"] ? $body : null;
            $response = Http::withHeaders([
                "x-client-id" => $PAYOS_CLIENT_ID,
                "x-api-key" => $PAYOS_API_KEY
            ])->post("https://api-merchant.payos.vn/v2/payment-requests/{$id}/cancel", $cancelBody)->json();

            $responseDataSignature = $this->createSignatureFromObj($PAYOS_CHECKSUM_KEY, $response["data"]);
            if ($responseDataSignature != $response["signature"]) {
                return $this->errorResponse("Chữ ký không khớp",  400);
            }
            $checkout = Checkout::find($id);
            $checkout->update([
                'status' => 'rejected'
            ]);
            $totalAmountPayable = number_format($request->amount, 0, ',', '.');
            $notication->postNotification(
                $request->user_id,
                'User',
                'Hoá đơn với số tiền: ' . $totalAmountPayable . 'đ đã bị huỷ',
                $request->room_id
            );

            return $this->successResponse("Cannel Payment link info success", $response["data"], 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }

    public function createSignaturePaymentRequest($checksumKey, $obj)
    {
        $dataStr = "amount={$obj["amount"]}&cancelUrl={$obj["cancelUrl"]}&description={$obj["description"]}&orderCode={$obj["orderCode"]}&returnUrl={$obj["returnUrl"]}";
        $signature = hash_hmac("sha256", $dataStr, $checksumKey);
        return $signature;
    }
    public function getAll()
    {
        try {

            $data = Checkout::all();

            return $this->successResponse('Tất cả Danh sách checkout thành công', CheckoutResource::collection($data), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function getByUser($userId)
    {
        try {

            $user = User::find($userId);
            if (!$user) {
                return $this->errorResponse('Tài khoản người dùng không đúng', 404);
            }
            $data = Checkout::where('user_id', $userId)->get();
            return $this->successResponse('Lấy danh sách checkout theo user thành công', CheckoutResource::collection($data), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function createSignatureFromObj($checksumKey, $obj)
    {
        ksort($obj);
        $queryStrArr = [];
        foreach ($obj as $key => $value) {
            if (in_array($value, ["undefined", "null"]) || gettype($value) == "NULL") {
                $value = "";
            }

            if (is_array($value)) {
                $valueSortedElementObj = array_map(function ($ele) {
                    ksort($ele);
                    return $ele;
                }, $value);
                $value = json_encode($valueSortedElementObj);
            }
            $queryStrArr[] = $key . "=" . $value;
        }
        $queryStr = implode("&", $queryStrArr);
        $signature = hash_hmac('sha256', $queryStr, $checksumKey);
        return $signature;
    }
}
