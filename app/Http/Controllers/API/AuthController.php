<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\PasswordHistory; // 🆕 WAJIB IMPORT INI
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required',
            'password' => 'required',
            'captcha_token' => 'required'
        ]);

        // Verifikasi Google reCAPTCHA
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->captcha_token,
            'remoteip' => $request->ip()
        ]);

        if (!$recaptchaResponse->json('success')) {
            return response()->json([
                'message' => 'Verifikasi reCAPTCHA gagal. Harap coba lagi.'
            ], 422);
        }

        $throttleKey = Str::transliterate(Str::lower($request->input('login_id')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'message' => 'Terlalu banyak percobaan masuk. Silakan coba lagi beberapa saat.'
            ], 429);
        }

        $user = User::where('email', $request->login_id)->orWhere('username', $request->login_id)->first();

        if ($user && $user->is_locked) {
            return response()->json([
                'message' => 'Akun Anda telah dinonaktifkan karena terlalu banyak kesalahan sandi. Silakan hubungi System Administrator.'
            ], 403);
        }

        if ($user && $user->is_active === false) {
            return response()->json([
                'message' => 'Akun Anda telah dinonaktifkan oleh Administrator. Silakan hubungi System Administrator.'
            ], 403);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey);

            if ($user) {
                $user->failed_attempts += 1;
                if ($user->failed_attempts >= 3) {
                    $user->is_locked = true;
                }
                $user->save();
            }

            AuditLog::create([
                'user_id' => $user ? $user->id : null,
                'action' => 'login_gagal',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => json_encode(['login_attempt' => $request->login_id])
            ]);

            return response()->json([
                'message' => 'Username/Email atau password salah.'
            ], 401);
        }

        // === JIKA LOGIN BERHASIL ===
        Auth::login($user);
        RateLimiter::clear($throttleKey);
        $user = Auth::user();
        $user->failed_attempts = 0;

        // 🆕 CEK USIA PASSWORD 90 HARI (Requirement No. 2)
        if ($user->password_changed_at) {
            // Hitung selisih hari dari terakhir ganti password sampai sekarang
            $daysSinceChange = now()->diffInDays($user->password_changed_at);

            if ($daysSinceChange >= 90) {
                // Kalau udah 90 hari, paksa React ngebuka halaman ChangePassword
                $user->is_first_login = true;
            }
        } else {
            // Kalau null (user lama/baru yg belum pernah di-set), paksa ganti
            $user->is_first_login = true;
        }

        $user->save();

        // -----------------------------------------------------

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login_sukses',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Login Berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'logout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    public function forceChangePassword(Request $request)
    {
        // 1. Validasi Standar Kompleksitas Password
        $request->validate([
            'password' => [
                'required', 'string', 'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ]
        ]);

        $user = $request->user();

        // 2. 🆕 CEK RIWAYAT 3 PASSWORD TERAKHIR (Requirement No. 2)
        // Ambil 3 data riwayat password terbaru dari user ini
        $recentPasswords = $user->passwordHistories()->latest()->take(3)->get();

        foreach ($recentPasswords as $history) {
            if (Hash::check($request->password, $history->password)) {
                return response()->json([
                    // ✅ Langsung tembak pesan aslinya ke 'message' biar gampang dibaca React
                    'message' => 'Password tidak boleh sama dengan 3 password terakhir yang pernah Anda gunakan!',
                    'errors' => [
                        'password' => ['Password tidak boleh sama dengan 3 password terakhir yang pernah Anda gunakan.']
                    ]
                ], 422);
            }
        }

        // 3. 🆕 EKSEKUSI PEMBARUAN JIKA LOLOS PENGECEKAN
        $newPasswordHash = Hash::make($request->password);

        $user->password = $newPasswordHash;
        $user->is_first_login = false;
        $user->password_changed_at = now(); // Reset timer 90 harinya ke HARI INI
        $user->save();

        // 4. 🆕 SIMPAN SANDI BARU KE DALAM TABEL RIWAYAT
        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => $newPasswordHash
        ]);

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
            'user' => $user
        ]);
    }
}
