<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ladumor\OneSignal\OneSignal;

class OneSinalController extends Controller
{
    public function pushNoticationApp($devieID, $message)
    {
        $fields['include_player_ids'] = [$devieID];

        OneSignal::sendPush($fields, $message);
    }

    public function getAllNotificationApp()
    {
        return OneSignal::getNotifications();
    }

    public function getAllNotificationAppByID($notiID)
    {
        return OneSignal::getNotification($notiID);
    }

    public function getALlDevice()
    {
        return OneSignal::getDevices();
    }

    public function getDeviceID($devieID)
    {
        return OneSignal::getDevice($devieID);
    }
}
