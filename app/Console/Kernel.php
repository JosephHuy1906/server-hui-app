<?php

namespace App\Console;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OneSinalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoomUserController;
use App\Models\Room;
use App\Models\RoomUser;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        $schedule->call(function () {
            $notication = new NotificationController();
            $oneSinal = new OneSinalController();
            $rooms = Room::withCount('users')->get();
            foreach ($rooms as $room) {
                if ($room->total_user == ($room->users_count - 1) && $room->status === 'Open') {
                    $room->update(['status' => 'Lock']);
                    $usersInRoom = $room->users;
                    foreach ($usersInRoom as $user) {
                        $notication->postNotification($user->id, $user->role, 'Phòng ' . $room->title . '  đã đủ người, đã bị khoá lại và đã bắt đầu chơi', $room->id);
                        if ($user->device_id !== null) {
                            $oneSinal->sendNoticationApp(
                                $user->device_id,
                                'Phòng ' . $room->title . ' đã đủ người, đã bị khoá lại và đã bắt đầu chơi'
                            );
                        }
                    }
                }
            }
        })->everySecond()->name('check-and-look-room')->withoutOverlapping();


        $schedule->call(function () {
            $notication = new NotificationController();
            $checkout = new CheckoutController();
            $oneSinal = new OneSinalController();
            $rooms = Room::where('payment_time', 'End day')
                ->where('status', 'Lock')
                ->get();
            foreach ($rooms as $room) {
                $userList = RoomUser::where('room_id', $room->id)
                    ->whereNotIn('user_id', ['4bdc395e-77d4-4602-8e0f-af6bb401560f'])
                    ->get();
                foreach ($userList as $us) {
                    $user = User::find($us->user_id);
                    $totalAmountPayable = number_format($room->price_room, 0, ',', '.');
                    $notication->postNotification($us->user_id, 'User', "Đã đến thời gian đóng tiền hụi phòng " . $room->title . ". Vui lòng thanh toán " . $totalAmountPayable . "đ", $room->id);
                    $checkout->postCheckout($room->price_room, 'Đóng tiền hụi phòng ' . $room->title, $us->id, $room->id, $us->user_id);
                    if ($user->device_id !== null) {
                        $oneSinal->sendNoticationApp(
                            $user->device_id,
                            "Đã đến thời gian đóng tiền hụi phòng " . $room->title . ". Vui lòng thanh toán " . $totalAmountPayable . "đ"
                        );
                    }
                }
            }
        })->dailyAt("08:15")->name('end_day_payment')->withoutOverlapping()->timezone('Asia/Ho_Chi_Minh');

        $schedule->call(function () {
            $notication = new NotificationController();
            $checkout = new CheckoutController();
            $oneSinal = new OneSinalController();
            $rooms = Room::where('payment_time', 'End of Month')
                ->where('status', 'Lock')
                ->get();
            foreach ($rooms as $room) {
                $userList = RoomUser::where('room_id', $room->id)
                    ->whereNotIn('user_id', ['4bdc395e-77d4-4602-8e0f-af6bb401560f'])
                    ->get();
                foreach ($userList as $us) {
                    $user = User::find($us->user_id);
                    $totalAmountPayable = number_format($room->price_room, 0, ',', '.');
                    $notication->postNotification($us->user_id, 'User', "Đã đến thời gian đóng tiền hụi phòng " . $room->title . ". Vui lòng thanh toán " . $totalAmountPayable . "đ", $room->id);
                    $checkout->postCheckout($room->price_room, 'Đóng tiền hụi phòng ' . $room->title, $us->id, $room->id, $us->user_id);
                    if ($user->device_id !== null) {
                        $oneSinal->sendNoticationApp(
                            $user->device_id,
                            "Đã đến thời gian đóng tiền hụi phòng " . $room->title . ". Vui lòng thanh toán " . $totalAmountPayable . "đ"
                        );
                    }
                }
            }
        })->monthlyOn(28, '17:00')->name('end_of_month_payment')->withoutOverlapping()->timezone('Asia/Ho_Chi_Minh');

        $schedule->call(function () {
            $notication = new NotificationController();
            $oneSinal = new OneSinalController();
            $room_user = new RoomUserController();
            $rooms = Room::where('status', 'Lock')
                ->get();
            $admin = User::where('role', "Admin")->get();
            foreach ($rooms as $room) {
                $check = $room_user->getUsersWithoutApprovedPayments($room->id);
                foreach ($check as $item) {
                    $notication->postNotification(
                        $item->user_id,
                        'User',
                        "Đã quá thời gian đóng tiền hụi phòng " . $room->title . ". Vui lòng thanh toán nếu không sẽ bị khoá tài khoản trong room",
                        $room->id
                    );
                    $notication->postNotification(
                        $item->user_id,
                        'Admin',
                        "User " . $item->user_id . ". Đã quá thời gian và chưa thanh toán tiền hụi phòng " . $room->title,
                        $room->id
                    );
                    if ($item->user->device_id !== null) {
                        $oneSinal->sendNoticationApp(
                            $item->user->device_id,
                            "Đã quá thời gian đóng tiền hụi phòng " . $room->title . ". Vui lòng thanh toán nếu không sẽ bị khoá tài khoản trong room"
                        );
                    }
                }
                foreach ($admin as $ad) {

                    Mail::send('emails.userNotPayment', compact('check', 'room', 'ad'), function ($email) use ($ad, $room) {
                        $email->to($ad->email, 'putapp')
                            ->subject('Danh sách user chưa đóng tiền hụi phòng ' . $room->title);
                    });
                }
            }
        })->dailyAt("19:00")->name('check_payment_day')->withoutOverlapping()->timezone('Asia/Ho_Chi_Minh');
    }


    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
