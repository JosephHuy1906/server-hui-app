<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function getImageUser($filename)
    {
        if (!Storage::exists("/public/images/users/{$filename}")) {
            abort(404);
        }
        $file = Storage::get("/public/images/users/{$filename}");
        $type = Storage::mimeType("/public/images/users/{$filename}");

        return response($file)->header('Content-Type', $type);
    }
    public function getImageRoom($filename)
    {
        if (!Storage::exists("/public/images/room/{$filename}")) {
            abort(404);
        }
        $file = Storage::get("/public/images/room/{$filename}");
        $type = Storage::mimeType("/public/images/room/{$filename}");
        return response($file)->header('Content-Type', $type);
    }


    public function uploadImage($file, $storagePath)
    {
        $filename = time() . '_' . Str::random(10) . '_' . $file->getClientOriginalName();
        $file->storeAs($storagePath, $filename, 'public');
        $baseUrl = Config::get('app.url');
        return $baseUrl . '/api/' . $storagePath . '/' . $filename;
    }
}
