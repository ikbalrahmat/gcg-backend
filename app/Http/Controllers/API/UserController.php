<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // 1. TAMPILIN SEMUA USER
    public function index(Request $request)
    {
        $userLogin = $request->user();

        // RBAC: Super Admin liat Admin SPI & Super Admin
        // Admin SPI liat Auditor, Auditee, Manajemen
        if ($userLogin->role === 'super_admin') {
            $users = User::whereIn('role', ['super_admin', 'admin_spi'])->get();
        } else if ($userLogin->role === 'admin_spi') {
            $users = User::whereIn('role', ['auditor', 'auditee', 'manajemen'])->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($users);
    }

    // 2. BIKIN USER BARU
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'role' => 'required|in:super_admin,admin_spi,auditor,auditee,manajemen',
            'level' => 'nullable|in:Anggota,Ketua Tim,Pengendali Teknis',
            'divisi' => 'nullable|string'
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'level' => $request->role === 'auditor' ? $request->level : null,
            'divisi' => $request->role === 'auditee' ? $request->divisi : null,
        ]);

        return response()->json(['message' => 'User berhasil dibuat', 'user' => $user], 201);
    }

    // 3. EDIT USER
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'role' => 'required|in:super_admin,admin_spi,auditor,auditee,manajemen',
        ];

        if ($request->filled('password')) {
            $rules['password'] = [
                'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'
            ];
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->level = $request->role === 'auditor' ? $request->level : null;
        $user->divisi = $request->role === 'auditee' ? $request->divisi : null;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'User berhasil diupdate', 'user' => $user]);
    }

    // 4. HAPUS USER
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'super_admin') {
            return response()->json(['message' => 'Super Admin tidak bisa dihapus'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus']);
    }

    // 5. BUKA GEMBOK AKUN (UNLOCK ACCOUNT) - 🆕 BARU
    public function unlockAccount($id)
    {
        $user = User::findOrFail($id);

        // Buka gembok dan reset angka kegagalan login
        $user->is_locked = false;
        $user->failed_attempts = 0;
        $user->save();

        return response()->json([
            'message' => 'Akun atas nama ' . $user->name . ' berhasil dibuka.',
            'user' => $user
        ]);
    }

    // 6. NONAKTIFKAN / AKTIFKAN USER MANUAL
    public function toggleActive($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'super_admin') {
            return response()->json(['message' => 'Super Admin tidak bisa dinonaktifkan'], 403);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return response()->json([
            'message' => 'Akun atas nama ' . $user->name . ' berhasil ' . $statusStr . '.',
            'user' => $user
        ]);
    }

    // 🆕 Jalur khusus ngambil daftar Auditor (Boleh diakses Ketua Tim & PT)
    public function getAuditors()
    {
        $auditors = \App\Models\User::where('role', 'auditor')->get();
        return response()->json($auditors);
    }

    // 🆕 Jalur khusus ngambil daftar Divisi Auditee untuk Kertas Kerja
    public function getDivisions()
    {
        $divisi = \App\Models\User::where('role', 'auditee')
                    ->whereNotNull('divisi')
                    ->pluck('divisi')
                    ->unique()
                    ->values();
        return response()->json($divisi);
    }
}
