<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function postPayment($user_id, $description, $price_pay)
    {

        $data = [
            'room_user_id' => $user_id,
            'description' => $description,
            'price_pay' => $price_pay,
        ];
        Payment::create($data);
        return  $this->successResponse('Thêm user dến hạn nộp tiền thành công', null, 201);
    }
    public function putPayment($status, $id)
    {
        $find = Payment::find($id);
        $update =  $find->update(['status' => $status]);
        return $this->successResponse('Cập nhập trạng thái thanh toán thành công', $update, 201);
    }
}
