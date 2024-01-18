<?php

namespace App\Console;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Models\Room;
use App\Models\RoomUser;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
            $payment = new PaymentController();
            $date = date('d/m/Y H:i:s');
            $rooms = Room::where('payment_time', 'End day')
                ->where('status', 'Lock')
                ->get();
            foreach ($rooms as $room) {
                $userList = RoomUser::where('room_id', $room->id)
                    ->whereNotIn('user_id', ['4bdc395e-77d4-4602-8e0f-af6bb401560f'])
                    ->get();
                foreach ($userList as $us) {
                    $notication->postNotification($us->user_id, 'User', "Đã đến thời gian đóng tiền hụi phòng " . $room->title, $room->id);
                    $payment->postPayment($us->id, 'Đến hạn đóng tiền hụi phòng ' . $room->title . ' ngày ' . $date, $room->price_room);
                }
            }
        })->everyMinute()->name('end_day_payment')->withoutOverlapping();

        $schedule->call(function () {
            $notication = new NotificationController();
            $payment = new PaymentController();
            $date = date('d/m/Y H:i:s');
            $rooms = Room::where('payment_time', 'End of Month')
                ->where('status', 'Lock')
                ->get();
            foreach ($rooms as $room) {
                $userList = RoomUser::where('room_id', $room->id)
                    ->whereNotIn('user_id', ['4bdc395e-77d4-4602-8e0f-af6bb401560f'])
                    ->get();
                foreach ($userList as $us) {
                    $notication->postNotification($us->user_id, 'User', "Đã đến thời gian đóng tiền hụi phòng " . $room->title, $room->id);
                    $payment->postPayment($us->id, 'Đến hạn đóng tiền hụi phòng ' . $room->title . ' ngày ' . $date, $room->price_room);
                }
            }
        })->monthlyOn(28, '18:00')->name('end_of_month_payment')->withoutOverlapping();
    }


    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
