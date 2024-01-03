<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function loginSocial(Request $request, $provider)
    {
        session(['redirect_social' => $request->get('redirect')]);
        return Socialite::driver($provider)->redirect();
    }
    public function callbackSocial(Request $request, $provider): RedirectResponse
    {
        $getInfo = Socialite::driver($provider)->user();
        dd([
            'email' => $getInfo->getEmail(),
            'username' => $getInfo->getId(),
            'fullname' => $getInfo->getName(),
            'image_url' => $getInfo->getAvatar(),
        ]);
    }
}
