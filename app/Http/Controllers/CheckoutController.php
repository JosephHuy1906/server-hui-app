<?php

namespace App\Http\Controllers;

use App\Http\Resources\CheckoutResource;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\User;
use App\Models\UserWinHui;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function getAll()
    {
        try {
            $data = Checkout::orderBy('created_at', 'desc')->get();
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
            $data = Checkout::where('user_id', $userId)->orderBy('id', 'desc')->get();
            return $this->successResponse('Lấy danh sách checkout theo user thành công', CheckoutResource::collection($data), 200);
        } catch (\Throwable $th) {
            return $this->errorResponse("Server Error",  500);
        }
    }
    public function updateStatus($id)
    {
        try {
            $checkout = Checkout::find($id);
            $notication = new NotificationController();
            if ($checkout->status === 'approved') {
                return;
            }
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
            if ($checkout->status === 'rejected') {
                return;
            }
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
            return view('cancel');
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
                'user_win_hui_id' => 'sometimes'
            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng', 400);
            }
            $oderID = intval(substr(strval(microtime(true) * 10000), -128));
            $oderCheck = Checkout::find($oderID);
            while ($oderCheck) {
                $oderID = intval(substr(strval(microtime(true) * 10000), -128));
                $oderCheck = Checkout::find($oderID);
            }
            $data = [
                "orderCode" => $oderID,
                "amount" => $request->amount,
                "description" => "Thanh toán hoá đơn",
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
            return $this->errorResponse($th->getMessage(),  500);
        }
    }

    public function postCheckout($price, $description, $room_user_id, $room_id, $user_id)
    {
        $payment = new PaymentController();
        $date = date('d/m/Y H:i:s');
        $oderID = str::random(10);

        $dataCheckOut = [
            "id" => $oderID,
            "price" => $price,
            "description" => $description,
            "user_id" => $user_id,
            'room_id' => $room_id,
        ];
        Checkout::create($dataCheckOut);
        $payment->postPayment(
            $oderID,
            $room_user_id,
            $user_id,
            'Đến hạn đóng tiền hụi  ngày ' . $date,
            $price,
            $room_id
        );
    }
    public function createPaymenRoomUsertLink(Request $request)
    {
        try {
            $baseUrl = env('APP_URL');
            $validator = Validator::make($request->all(), [
                'amount' => 'required',
                'description' => 'required',
                'order_id' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Thông tin truyền vào chưa đúng', 400);
            }
            $oderCode = intval(substr(strval(microtime(true) * 10000), -128));
            $oderCheck = Checkout::find($oderCode);
            while ($oderCheck) {
                $oderCode = intval(substr(strval(microtime(true) * 10000), -128));
                $oderCheck = Checkout::find($oderCode);
            }
            $data = [
                "orderCode" => $oderCode,
                "amount" => $request->amount,
                "description" => $request->description,
                "returnUrl" => $baseUrl . "/successRoom",
                "cancelUrl" => $baseUrl . "/cancelRoom"
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
            $oder = Checkout::find($request->order_id);
            $payment = Payment::find($request->order_id);
            if ($oder) {
                $oder->update([
                    'id' => $oderCode,
                ]);
                $payment->update([
                    'id' => $oderCode,
                ]);
            }
            return $this->successResponse(
                'Create Payment link success',
                ['bankURL' => $response["data"]["checkoutUrl"]],
                201
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(),  500);
        }
    }

    public function handlePaymentSuccessRoom(Request $request)
    {
        try {
            $orderCode = $request->input('orderCode');
            $payment = Payment::find($orderCode);
            $user = RoomUser::find($payment->room_user_id);
            $checkout = Checkout::find($orderCode);
            $notication = new NotificationController();
            $data = User::find($user->user_id);
            $date = date('d/m/Y H:i:s');
            if ($payment->status === 'approved') {
                return view('successRoom');
            } else {
                $payment->update([
                    'status' => 'approved'
                ]);
                $checkout->update([
                    'status' => 'approved'
                ]);
                $room = Room::find($payment->room_id);
                $room->accumulated_amount += $payment->price_pay;
                $room->save();
                $notication->postNotification(
                    $user->user_id,
                    'User',
                    'Bạn đã đóng   ' . $payment->price_pay . 'đ tiền hụi ngày ' . $date . ' Thành công',
                    $payment->room_id
                );
                if ($data->device_id !== null) {
                    $this->sendNoticationApp(
                        $data->device_id,
                        'Bạn đã đóng   ' . $payment->price_pay . 'đ tiền hụi ngày ' . $date . ' Thành công'
                    );
                }
                Mail::send('emails.paymentAuction', compact('checkout', 'data'), function ($email) use ($data) {
                    $email->to($data->email, 'putapp')
                        ->subject('Thanh toán tiền phòng hụi ');
                });
                return view('successRoom');
            }
        } catch (\Throwable $th) {
            return $this->errorResponse('Server Error', 500);
        }
    }
    public function handlePaymentCancelRoom(Request $request)
    {
        try {
            $orderCode = $request->input('orderCode');
            $notication = new NotificationController();
            $payment = Payment::find($orderCode);
            $checkout = Checkout::find($orderCode);
            $user = RoomUser::find($payment->room_user_id);
            $data = User::find($user->user_id);
            $date = date('d/m/Y H:i:s');
            if ($payment->status === 'rejected') {
                return view('cancelRoom');
            } else {
                $payment->update([
                    'status' => 'rejected'
                ]);
                // $checkout->update([
                //     'status' => 'rejected'
                // ]);
                $notication->postNotification(
                    $user->user_id,
                    'User',
                    'Bạn vẫn chưa đóng   ' . $payment->price_pay . 'đ tiền hụi ngày ' . $date,
                    $payment->room_id
                );
                if ($data->device_id !== null) {
                    $this->sendNoticationApp(
                        $data->device_id,
                        'Bạn vẫn chưa đóng   ' . $payment->price_pay . 'đ tiền hụi ngày ' . $date,
                    );
                }

                Mail::send('emails.paymentAuction', compact('checkout', 'data'), function ($email) use ($data) {
                    $email->to($data->email, 'putapp')
                        ->subject('Thanh toán tiền phòng hụi ');
                });
                return view('cancelRoom');
            }
        } catch (\Throwable $th) {
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function handlePaymentSuccessAuctionHui(Request $request)
    {
        try {
            $orderCode = $request->input('orderCode');
            $checkout = Checkout::find($orderCode);
            $notication = new NotificationController();
            if ($checkout->status === 'approved') {
                return;
            }
            $checkout->update([
                'status' => 'approved'
            ]);
            $find = UserWinHui::find($checkout->user_win_hui_id);
            $room = Room::find($checkout->room_id);
            $data = User::find($find->user_id);
            $admin = User::where('role', 'Admin')->get();
            if ($find) {
                $find->update([
                    'status_user' => 'approved'
                ]);
                $room->update([
                    'accumulated_amount' => 0
                ]);

                $totalAmountPayable = number_format($find->total_amount_payable, 0, ',', '.');
                $notication->postNotification(
                    $find->user_id,
                    'user',
                    'Bạn đã thanh toán ' . $totalAmountPayable . 'đ',
                    $find->room_id
                );
                $notication->postNotification(
                    $find->user_id,
                    'admin',
                    'User có id là: ' . $find->user_id . ' đã thanh toán tiền' . $totalAmountPayable . 'đ',
                    $find->room_id
                );
                if ($data->device_id !== null) {
                    $this->sendNoticationApp(
                        $data->device_id,
                        'Bạn đã thanh toán ' . $totalAmountPayable . 'đ'
                    );
                }
                foreach ($admin as $ad) {
                    Mail::send('emails.paymentAuctionAdmin', compact('checkout', 'data'), function ($email) use ($ad) {
                        $email->to($ad->email, 'putapp')
                            ->subject('Hoá đơn thanh toán của hội viên');
                    });
                }

                Mail::send('emails.paymentAuction', compact('checkout', 'data'), function ($email) use ($data) {
                    $email->to($data->email, 'putapp')
                        ->subject('Thanh toán tiền đấu giá hụi');
                });
            }

            return view('success');
        } catch (\Throwable $th) {
            return $this->errorResponse('Server Error', 500);
        }
    }

    public function handlePaymentCancelAuctionHui(Request $request)
    {
        try {
            $orderCode = $request->input('orderCode');
            $checkout = Checkout::find($orderCode);
            $notication = new NotificationController();
            if ($checkout->status === 'rejected') {
                return view('cancel');
            }
            $checkout->update([
                'status' => 'rejected'
            ]);
            $find = UserWinHui::find($checkout->user_win_hui_id);
            $data = User::find($find->user_id);
            $room = Room::find($find->room_id);
            $admin = User::where('role', 'Admin')->get();
            if ($find) {
                // $find->update([
                //     'status_user' => 'rejected'
                // ]);
                // $room->update([
                //     'accumulated_amount' => $find->total_auction
                // ]);
                $totalAmountPayable = number_format($checkout->price, 0, ',', '.');
                $notication->postNotification(
                    $checkout->user_id,
                    'User',
                    'Bạn đã huỷ hoá đơn thanh toán đấu hụi với số tiền ' . $totalAmountPayable . 'đ vui lòng thanh toán lại để nhận tiền đấu giá.',
                    $find->room_id
                );
                $notication->postNotification(
                    $checkout->user_id,
                    'Admin',
                    'User ' . $checkout->user_id . ' đã huỷ hoá đơn' . $totalAmountPayable . 'đ ',
                    $find->room_id
                );
                if ($data->device_id !== null) {
                    $this->sendNoticationApp(
                        $data->device_id,
                        'Bạn đã huỷ hoá đơn thanh toán đấu hụi với số tiền ' . $totalAmountPayable . 'đ vui lòng thanh toán lại để nhận tiền đấu giá.',
                    );
                }
                foreach ($admin as $ad) {
                    Mail::send('emails.paymentAuctionAdmin', compact('checkout', 'data'), function ($email) use ($ad) {
                        $email->to($ad->email, 'putapp')
                            ->subject('Hoá đơn thanh toán đấu giá hụi của hội viên đã huỷ');
                    });
                }

                Mail::send('emails.paymentAuction', compact('checkout', 'data'), function ($email) use ($data) {
                    $email->to($data->email, 'putapp')
                        ->subject('Hủy thanh toán tiền đấu giá hụi');
                });
            }

            return view('cancel');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(),  500);
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
