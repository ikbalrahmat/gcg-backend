<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuditLogController;
use App\Http\Controllers\API\MasterDataController;
use App\Http\Controllers\API\AssessmentController;
use App\Http\Controllers\API\FileMonitoringController;

// Route Terbuka (Nggak butuh token)
Route::post('/login', [AuthController::class, 'login']);

// Route Tertutup (Wajib Login Dulu)
Route::middleware('auth:sanctum')->group(function () {

    // Endpoint ambil profil sendiri
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Endpoint logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Endpoint CRUD User (WAJIB DI DALAM SINI)
    Route::apiResource('users', UserController::class);

    // 🆕 Jalur khusus ambil list Auditor
    Route::get('/auditors', [UserController::class, 'getAuditors']);

    // 🆕 Jalur khusus ambil list Divisi (Buat Dropdown Kertas Kerja)
    Route::get('/divisions', [UserController::class, 'getDivisions']);

    // ENDPOINT UNTUK BUKA GEMBOK AKUN
    Route::post('/users/{id}/unlock', [UserController::class, 'unlockAccount']);

    Route::post('/change-password', [AuthController::class, 'forceChangePassword']);

    // Endpoint Audit Log
    Route::get('/audit-logs', [AuditLogController::class, 'index']);

    // Endpoint narik Master Data GCG
    Route::get('/master-indicators', [MasterDataController::class, 'index']);

    // Endpoint buat nge-Save (Sync) Master Data GCG
    Route::post('/master-indicators/sync', [MasterDataController::class, 'sync']);

    // Endpoint CRUD Assessment & Jadwal
    Route::apiResource('assessments', AssessmentController::class);

    // Endpoint khusus untuk simpan nilai Kertas Kerja (Data JSON)
    Route::put('/assessments/{id}/data', [AssessmentController::class, 'updateData']);

    // --- 🆕 JALUR KHUSUS FILE & MONITORING ---
    Route::get('/evidences', [FileMonitoringController::class, 'getEvidences']);
    Route::post('/evidences', [FileMonitoringController::class, 'uploadEvidence']);
    Route::delete('/evidences/{id}', [FileMonitoringController::class, 'deleteEvidence']);
    Route::put('/evidences/{id}/status', [FileMonitoringController::class, 'updateEvidenceStatus']);

    Route::get('/document-requests', [FileMonitoringController::class, 'getDocumentRequests']);
    Route::post('/document-requests', [FileMonitoringController::class, 'createDocumentRequest']);
    Route::put('/document-requests/{id}', [FileMonitoringController::class, 'updateDocumentRequest']);

    Route::get('/tl-records', [FileMonitoringController::class, 'getTlRecords']);
    Route::post('/tl-records/{id}', [FileMonitoringController::class, 'upsertTlRecord']);
    Route::post('/evidences/{id}/copy', [FileMonitoringController::class, 'copyArchiveEvidence']);

    // Endpoint khusus untuk upload Laporan Final
    Route::post('/assessments/{id}/upload-report', [AssessmentController::class, 'uploadFinalReport']);


});
