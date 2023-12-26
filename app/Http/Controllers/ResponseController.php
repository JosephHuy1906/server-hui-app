<?php

namespace App\Http\Controllers;


class ResponseController extends Controller
{
    public function successResponse($message, $data = null, $status)
    {
        return response()->json([
            'status' => $status,
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function errorResponse($message, $err = null, $status)
    {
        return response()->json([
            'status' => $status,
            'success' => false,
            'message' => $message,
            'error' => $err
        ], $status);
    }
}
