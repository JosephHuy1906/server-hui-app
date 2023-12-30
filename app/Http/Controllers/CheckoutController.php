<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use App\Models\UserWinHui;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Ramsey\Uuid\Uuid;

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
            $find->update([
                'status' => 'paid'
            ]);
            $totalAmountPayable = number_format($find->total_amount_payable, 0, ',', '.') . ' đ';
            $notication->postNotification($find->user_id, 'user', 'Bạn đã thanh toán ' . $totalAmountPayable . ' tiền đấu hụi', $find->room_id);
            $notication->postNotification($find->user_id, 'admin', 'User đã thanh toán tiền' . $totalAmountPayable . ' đấu hụi', $find->room_id);
        } catch (\Throwable $e) {
            return $response->errorResponse('Server Error', $e->getMessage(), 500);
        }
    }
}
