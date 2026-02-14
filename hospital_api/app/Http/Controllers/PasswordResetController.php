<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Patient;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        $user = Patient::where('email', $email)->first()
            ?? Doctor::where('email', $email)->first()
            ?? Admin::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'We could not find a user with that email address.'], 404);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = env('FRONTEND_URL', 'http://localhost:3000') . "/reset-password?token={$token}&email=" . urlencode($email);

        try {
            Mail::to($email)->send(new ResetPasswordMail($resetUrl));
            return response()->json(['message' => 'Reset link sent to your email.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send email. please try again later.' . $e->getMessage()], 500);
        }
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        // Token is valid for 1 hour
        if (now()->subHour() > $reset->created_at) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Token has expired.'], 400);
        }

        $user = Patient::where('email', $request->email)->first()
            ?? Doctor::where('email', $request->email)->first()
            ?? Admin::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully.'], 200);
    }
}
