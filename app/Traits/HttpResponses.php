<?php

namespace App\Traits;

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
}
