<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Keamanan: Pastikan cuma Admin yang bisa liat log
        if (!in_array($request->user()->role, ['super_admin', 'admin_spi'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Ambil 500 log terbaru beserta data user yang ngelakuinnya
        $logs = AuditLog::with('user:id,name,role')->latest()->take(500)->get();

        return response()->json($logs);
    }
}
