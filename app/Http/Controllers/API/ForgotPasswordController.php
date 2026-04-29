<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\PasswordHistory;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email tidak terdaftar di sistem.'], 404);
        }

        $token = Str::random(60);

        // Delete existing tokens
        DB::table('password_resets')->where('email', $request->email)->delete();

        // Insert new token
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);

        // URL to frontend reset password page
        // Assuming VITE_BASE_URL is the frontend URL, but in local it is usually localhost:5173
        // Let's pass the URL explicitly
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $resetUrl = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);

        try {
            Mail::send([], [], function ($message) use ($user, $resetUrl) {
                $message->to($user->email)
                    ->subject('Reset Password G-AS')
                    ->html("
                        <h2>Halo, {$user->name}</h2>
                        <p>Kami menerima permintaan untuk mereset kata sandi akun Anda di aplikasi G-AS.</p>
                        <p>Silakan klik tombol di bawah ini untuk mengatur ulang kata sandi Anda:</p>
                        <a href=\"{$resetUrl}\" style=\"display:inline-block;padding:10px 20px;color:#fff;background-color:#4f46e5;text-decoration:none;border-radius:5px;\">Reset Password</a>
                        <p>Jika Anda tidak meminta pengaturan ulang kata sandi, abaikan email ini.</p>
                        <p>Link ini akan kedaluwarsa dalam 60 menit.</p>
                        <br>
                        <p>Terima kasih,<br>Tim G-AS</p>
                    ");
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengirim email: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Email reset password telah dikirim!']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => [
                'required', 'string', 'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ]
        ]);

        $resetRecord = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json(['message' => 'Token tidak valid atau sudah kedaluwarsa.'], 400);
        }

        // Check expiration (e.g. 60 mins)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Token sudah kedaluwarsa.'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        // Check recent passwords
        $recentPasswords = $user->passwordHistories()->latest()->take(3)->get();
        foreach ($recentPasswords as $history) {
            if (Hash::check($request->password, $history->password)) {
                return response()->json([
                    'message' => 'Password tidak boleh sama dengan 3 password terakhir yang pernah Anda gunakan!',
                    'errors' => [
                        'password' => ['Password tidak boleh sama dengan 3 password terakhir yang pernah Anda gunakan.']
                    ]
                ], 422);
            }
        }

        // Update password
        $newPasswordHash = Hash::make($request->password);
        $user->password = $newPasswordHash;
        $user->is_first_login = false;
        $user->password_changed_at = now();
        // Unlock user if they were locked due to attempts
        $user->is_locked = false;
        $user->failed_attempts = 0;
        $user->save();

        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => $newPasswordHash
        ]);

        // Delete the token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password berhasil direset!']);
    }
}
