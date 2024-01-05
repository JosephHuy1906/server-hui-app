<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\UserWinHui;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Ramsey\Uuid\Uuid;
use SignatureUtils;

use function Laravel\Prompts\alert;

class CheckoutController extends Controller
{
    public function vnpay_checkout(Request $request, $user_id, $price, $description)
    {
        $response = new ResponseController();
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = url('/vnpay-callback');
        $vnp_TmnCode = "4PCPLZUF";
        $vnp_HashSecret = "WXMRJLAHAMSUZVUWBTVISXIGFKSMJAOM";
        $vnp_TxnRef = Uuid::uuid4()->toString();
        $vnp_BankCode = 'VISA';
        $vnp_IpAddr = $request->ip();
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $price * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => 'VN',
            "vnp_OrderInfo" => $description,
            "vnp_OrderType" => 'Hui pay',
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => now(),

        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;

        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Chuyển thành mảng chứa URL thanh toán
        $checkoutData = [
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url,
        ];

        if ($request->has('redirect')) {
            return redirect()->away($vnp_Url);
        } else {
            Checkout::create([
                'id' => $vnp_TxnRef,
                'user_id' => $user_id,
                'price' => $price,
                'description' => $description,
                'status' => 'pending'
            ]);
            return response()->json(['data' => $checkoutData]);
        }
    }

    public function checkout(Request $request)
    {
        $response = new ResponseController();
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = url('/vnpay-callback');
        $vnp_TmnCode = "4PCPLZUF";
        $vnp_HashSecret = "WXMRJLAHAMSUZVUWBTVISXIGFKSMJAOM";

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'price' => 'required',
            'description' => 'required',
            'user_win_hui_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $response->errorResponse('Input value error', $validator->errors(), 400);
        }

        $vnp_BankCode = 'NCB';


        $vnp_IpAddr = $request->ip();
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

        $vnp_TxnRef = rand(1, 10000);

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $request->price * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => 'VN',
            "vnp_OrderInfo" => $request->description,
            "vnp_Bill_Mobile" => $request->user_win_hui_id,
            "vnp_OrderType" => 'Hui pay',
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;

        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        $returnData = array(
            'code' => '00', 'message' => 'success', 'data' => $vnp_Url
        );

        if ($request->has('redirect')) {
            return redirect()->away($vnp_Url);
        } else {
            Checkout::create([
                'id' => $vnp_TxnRef,
                'user_id' => $request->user_id,
                'price' => $request->price,
                'description' => $request->description,
                'status' => 'pending',
                'user_win_hui_id' => $request->user_win_hui_id,
            ]);
            return $response->successResponse('Thanh toán hoá đơn', $returnData, 200);
        }
    }

    public function handleCallback(Request $request)
    {

        $transactionId = $request->input('vnp_TxnRef');
        $status = $request->input('vnp_ResponseCode');
        $price = $request->input('vnp_Amount');
        $description = $request->input('vnp_OrderInfo');
        $id_hui = $request->input('vnp_Bill_Mobile');


        $this->updateStatus($transactionId);
        return View::make('bill_details', [
            'transactionId' => $transactionId,
            'status' => $status,
            'price' => $price,
            'description' => $description,
            'id_hui' => $id_hui,
        ]);
    }

    public function updateStatus($id)
    {
        try {
            $response = new ResponseController();
            $checkout = Checkout::find($id);
            $notication = new NotificationController();
            $checkout->update([
                'status' => 'approved'
            ]);
            $find = UserWinHui::find($checkout->user_win_hui_id);
            if ($find) {
                $find->update([
                    'status' => 'paid'
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
            return $response->errorResponse('Server Error', $e->getMessage(), 500);
        }
    }
    public function updateStatusReject($id)
    {
        try {
            $response = new ResponseController();
            $checkout = Checkout::find($id);
            $notication = new NotificationController();
            $checkout->update([
                'status' => 'rejected'
            ]);
            $find = UserWinHui::find($checkout->user_win_hui_id);
            if ($find) {
                $find->update([
                    'status' => 'paid'
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
            return $response->errorResponse('Server Error', $e->getMessage(), 500);
        }
    }

    public function createPaymentLink(Request $request)
    {
        try {
            $respon = new ResponseController();
            $notication = new NotificationController();
            $baseUrl = env('APP_URL');
            $validator = Validator::make($request->all(), [
                'amount' => 'required',
                'description' => 'required',
                'user_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $respon->errorResponse('Input value error', $validator->errors(), 400);
            }
            $oderID = intval(substr(strval(microtime(true) * 10000), -6));
            $data = [
                "orderCode" => $oderID,
                "amount" => $request->amount,
                "description" => $request->description,
                "returnUrl" => $baseUrl . "/success",
                "cancelUrl" => $baseUrl . "/cancel"
            ];
            $dataCheckOut = [
                "id" => $oderID,
                "price" => $request->amount,
                "description" => $request->description,
                "user_id" => $request->user_id,
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
                return $respon->errorResponse("Signature not match", null, 404);
            }
            Checkout::create($dataCheckOut);
            $totalAmountPayable = number_format($request->amount, 0, ',', '.');
            $notication->postNotification(
                $request->user_id,
                'User',
                'Hoá đơn với số tiền: ' . $totalAmountPayable . 'đ đang chờ bạn thanh toán',
                $request->room_id
            );
            return $respon->successResponse(
                'Create Payment link success',
                ['bankURL' => $response["data"]["checkoutUrl"]],
                201
            );
        } catch (\Throwable $th) {
            return $respon->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function getPaymentLinkInfoOfOrder($id)
    {
        try {
            $respon = new ResponseController();
            $PAYOS_CLIENT_ID = env('PAYOS_CLIENT_ID');
            $PAYOS_API_KEY = env('PAYOS_API_KEY');
            $PAYOS_CHECKSUM_KEY = env('PAYOS_CHECKSUM_KEY');

            $response = Http::withHeaders([
                "x-client-id" => $PAYOS_CLIENT_ID,
                "x-api-key" => $PAYOS_API_KEY
            ])->get("https://api-merchant.payos.vn/v2/payment-requests/{$id}")->json();

            $responseDataSignature = $this->createSignatureFromObj($PAYOS_CHECKSUM_KEY, $response["data"]);
            if ($responseDataSignature != $response["signature"]) {
                return $respon->errorResponse("Signature not match", null, 400);
            }
            return $respon->successResponse("Get Payment link info success", $response["data"], 200);
        } catch (\Throwable $th) {
            return $respon->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }
    public function cancelPaymentLinkOfOrder(Request $request, String $id)
    {
        try {
            $notication = new NotificationController();
            $respon = new ResponseController();
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
                return $respon->errorResponse("Signature not match", null, 400);
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

            return $respon->successResponse("Cannel Payment link info success", $response["data"], 200);
        } catch (\Throwable $th) {
            return $respon->errorResponse("Server Error", $th->getMessage(), 500);
        }
    }

    public function createSignaturePaymentRequest($checksumKey, $obj)
    {
        $dataStr = "amount={$obj["amount"]}&cancelUrl={$obj["cancelUrl"]}&description={$obj["description"]}&orderCode={$obj["orderCode"]}&returnUrl={$obj["returnUrl"]}";
        $signature = hash_hmac("sha256", $dataStr, $checksumKey);
        return $signature;
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
