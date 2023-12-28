<?php

namespace App\Http\Controllers;

use App\Models\Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class CheckoutController extends Controller
{
    public function vnpay_checkout(Request $request)
    {
        $response = new ResponseController();
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = "https://localhost/vnpay_php/vnpay_return.php";
        $vnp_TmnCode = "4PCPLZUF";
        $vnp_HashSecret = "WXMRJLAHAMSUZVUWBTVISXIGFKSMJAOM";

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'price' => 'required',
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            return $response->errorResponse('Input value error', $validator->errors(), 400);
        }

        $vnp_TxnRef = Uuid::uuid4()->toString();
        $vnp_BankCode = 'VISA';


        $vnp_IpAddr = $request->ip();
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];



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
            "vnp_OrderType" => 'Hui pay',
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        // if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
        //     $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        // }

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

        // $returnData = ['code' => '00', 'message' => 'success', 'data' => $vnp_Url];

        if ($request->has('redirect')) {
            return redirect()->away($vnp_Url);
        } else {
            Checkout::create([
                'id' => $vnp_TxnRef,
                'user_id' => $request->user_id,
                'price' => $request->price,
                'description' => $request->description,
                'status' => 'pending'
            ]);
            return $response->successResponse('Thanh toán hoá đơn', $vnp_Url, 200);
        }
    }
}
