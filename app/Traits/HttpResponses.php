<?php

namespace App\Traits;

use Ladumor\OneSignal\OneSignal;

trait HttpResponses
{
    protected function successResponse($message, $data = null, $status)
    {
        return response()->json([
            'status' => $status,
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }
    protected function errorResponse($message, $status)
    {
        return response()->json([
            'status' => $status,
            'success' => false,
            'message' => $message,
        ], $status);
    }
    // protected function sendNoticationApp($devieID, $message)
    // {
    //     $fields['include_player_ids'] = [$devieID];

    //     OneSignal::sendPush($fields, $message);
    // }
    public function sendNoticationApp($devieID, $message, $location)
    {
        $fields['include_player_ids'] = [$devieID];
        $fields['contents'] = array(
            "message" => $message,
            "location" => $location,
        );
        OneSignal::sendPush($fields);
    }
}
