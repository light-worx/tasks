<?php

namespace App\Http\Controllers\Pwa;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Lightworx\FilamentPwa\Mail\EmailVerificationPin;
use Lightworx\FilamentPwa\Models\UserDevice;
use Lightworx\FilamentPwa\Models\UserPreference;

class EmailVerificationController
{
    public function sendPin(Request $request)
    {
        $data = $request->validate(['device_id' => 'required|string', 'email' => 'required|email']);

        $key = 'pwa-email-pin:' . $data['device_id'];
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json(['message' => 'Too many attempts, try again shortly.'], 429);
        }
        RateLimiter::hit($key, 600);

        $pin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $preference = UserPreference::updateOrCreate(
            ['email' => $data['email']],
            ['phone_verification_pin' => $pin, 'pin_expires_at' => now()->addMinutes(15)]
        );
        UserDevice::firstOrCreate(['device_id' => $data['device_id']]);

        Mail::to($data['email'])->send(new EmailVerificationPin($pin, config('pwa.app_name')));

        return response()->json(['status' => 'sent']);
    }

    public function verifyPin(Request $request)
    {
        $data = $request->validate(['device_id' => 'required|string', 'pin' => 'required|string|size:4']);

        $preference = UserPreference::where('phone_verification_pin', $data['pin'])
            ->where('pin_expires_at', '>', now())->first();

        if (! $preference) {
            return response()->json(['message' => 'Incorrect or expired code.'], 422);
        }

        $preference->update([
            'email_verified_at' => now(), 
            'phone_verified'         => true,
            'phone_verification_pin' => null, 
            'pin_expires_at' => null
        ]);

        $device = UserDevice::firstOrCreate(['device_id' => $data['device_id']]);
        $device->update(['user_preference_id' => $preference->id]);

        return response()->json(['status' => 'verified', 'email' => $preference->email]);
    }
}