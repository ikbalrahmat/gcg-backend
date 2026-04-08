<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Evidence;
use App\Models\DocumentRequest;
use App\Models\TlRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileMonitoringController extends Controller
{
    // =================== EVIDENCE (FILE UPLOAD KERTAS KERJA) ===================
    public function getEvidences() {
        return response()->json(Evidence::all()->map(fn($e) => $this->formatEvidence($e)));
    }

    public function uploadEvidence(Request $request) {
        try {
            // 🔧 FIX: Cek apakah file benar-benar sampai ke server
            if (!$request->hasFile('file')) {
                return response()->json(['message' => 'File tidak terdeteksi. Pastikan ukuran file tidak melebihi limit server.'], 400);
            }

            $request->validate(['file' => 'required|file', 'id' => 'required']);

            $file = $request->file('file');
            $path = $file->store('evidences', 'public');

            // 🔧 FIX: Penentuan status dokumen otomatis 'Verified' jika yang upload Auditor
            $statusDokumen = ($request->divisi === 'Auditor (Internal)') ? 'Verified' : 'Menunggu Verifikasi';

            $evidence = Evidence::create([
                'id' => $request->id,
                'assessment_id' => $request->assessmentId,
                'assessment_year' => $request->assessmentYear,
                'aspect_id' => $request->aspectId,
                'indicator_id' => $request->indicatorId,
                'parameter_id' => $request->parameterId,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'divisi' => $request->divisi,
                'upload_date' => $request->uploadDate,
                'status' => $statusDokumen // <- Memakai variabel logika status
            ]);

            return response()->json(['message' => 'File Uploaded', 'evidence' => $this->formatEvidence($evidence)]);
        } catch (\Exception $e) {
            // 🔧 FIX: Tangkap error 500
            return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteEvidence($id) {
        $evidence = Evidence::findOrFail($id);
        if ($evidence->file_path) {
            Storage::disk('public')->delete($evidence->file_path);
        }
        $evidence->delete();
        return response()->json(['message' => 'File Deleted']);
    }

    public function updateEvidenceStatus(Request $request, $id) {
        $evidence = Evidence::findOrFail($id);
        $evidence->update(['status' => $request->status]);
        return response()->json(['message' => 'Status Updated']);
    }

    private function formatEvidence($e) {
        return [
            'id' => $e->id, 'assessmentId' => $e->assessment_id, 'assessmentYear' => $e->assessment_year,
            'aspectId' => $e->aspect_id, 'indicatorId' => $e->indicator_id, 'parameterId' => $e->parameter_id,
            'fileName' => $e->file_name, 'fileUrl' => asset('storage/' . $e->file_path),
            'divisi' => $e->divisi, 'uploadDate' => $e->upload_date, 'status' => $e->status
        ];
    }

    public function copyArchiveEvidence(Request $request, $id) {
        $request->validate([
            'newAssessmentId' => 'required',
            'newAssessmentYear' => 'required',
            'newId' => 'required'
        ]);

        try {
            // Cari dokumen lama
            $oldEvidence = Evidence::findOrFail($id);

            // Pastikan file fisiknya ada di storage
            if (!Storage::disk('public')->exists($oldEvidence->file_path)) {
                return response()->json(['message' => 'File fisik arsip tidak ditemukan di server.'], 404);
            }

            // Buat nama path baru untuk file hasil copy
            $extension = pathinfo($oldEvidence->file_path, PATHINFO_EXTENSION);
            $newPath = 'evidences/copy_' . time() . '_' . uniqid() . '.' . $extension;

            // Copy fisik file di dalam storage server
            Storage::disk('public')->copy($oldEvidence->file_path, $newPath);

            // Simpan record baru ke database untuk tahun ini
            $newEvidence = Evidence::create([
                'id' => $request->newId,
                'assessment_id' => $request->newAssessmentId,
                'assessment_year' => $request->newAssessmentYear,
                'aspect_id' => $oldEvidence->aspect_id,
                'indicator_id' => $oldEvidence->indicator_id,
                'parameter_id' => $oldEvidence->parameter_id,
                'file_name' => $oldEvidence->file_name, // Nama file asli dipertahankan
                'file_path' => $newPath,
                'divisi' => 'Auditor (Disalin dari Arsip TB ' . $oldEvidence->assessment_year . ')',
                'upload_date' => now()->format('Y-m-d'),
                'status' => 'Verified' // Langsung otomatis verified karena dari arsip
            ]);

            return response()->json([
                'message' => 'Berhasil menyalin dokumen dari arsip.',
                'evidence' => $this->formatEvidence($newEvidence)
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    // =================== DOCUMENT REQUESTS (TAGIHAN) ===================
    public function getDocumentRequests() {
        return response()->json(DocumentRequest::all()->map(fn($r) => $this->formatRequest($r)));
    }

    public function createDocumentRequest(Request $request) {
        $req = DocumentRequest::create([
            'id' => $request->id, 'assessment_id' => $request->assessmentId, 'assessment_year' => $request->assessmentYear,
            'aspect_id' => $request->aspectId, 'indicator_id' => $request->indicatorId, 'parameter_id' => $request->parameterId,
            'parameter_name' => $request->parameterName, 'target_divisi' => $request->targetDivisi,
            'requested_by' => $request->requestedBy, 'request_date' => $request->requestDate, 'status' => 'Requested', 'note' => $request->note
        ]);
        return response()->json(['message' => 'Request Created', 'request' => $this->formatRequest($req)]);
    }

    public function updateDocumentRequest(Request $request, $id) {
        $req = DocumentRequest::findOrFail($id);
        $req->update(['status' => $request->status, 'note' => $request->note]);
        return response()->json(['message' => 'Request Updated']);
    }

    private function formatRequest($r) {
        return [
            'id' => $r->id, 'assessmentId' => $r->assessment_id, 'assessmentYear' => $r->assessment_year,
            'aspectId' => $r->aspect_id, 'indicatorId' => $r->indicator_id, 'parameterId' => $r->parameter_id,
            'parameterName' => $r->parameter_name, 'targetDivisi' => $r->target_divisi,
            'requestedBy' => $r->requested_by, 'requestDate' => $r->request_date, 'status' => $r->status, 'note' => $r->note
        ];
    }

    // =================== TL RECORDS (MONITORING) ===================
    public function getTlRecords() {
        $records = TlRecord::all()->keyBy('id')->map(function ($t) {
            return [
                'status' => $t->status,
                'fileName' => $t->file_name,
                'fileUrl' => $t->file_path ? asset('storage/' . $t->file_path) : null,
                'auditeeNote' => $t->auditee_note,
                'auditorNote' => $t->auditor_note,
            ];
        });
        return response()->json($records);
    }

    public function upsertTlRecord(Request $request, $id) {
        $tl = TlRecord::firstOrNew(['id' => $id]);
        $tl->assessment_id = $request->assessmentId;
        $tl->status = $request->status;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if ($tl->file_path) Storage::disk('public')->delete($tl->file_path);
            $tl->file_path = $file->store('tl_evidences', 'public');
            $tl->file_name = $file->getClientOriginalName();
        }

        if ($request->has('auditeeNote')) $tl->auditee_note = $request->auditeeNote;
        if ($request->has('auditorNote')) $tl->auditor_note = $request->auditorNote;

        $tl->save();
        return response()->json(['message' => 'TL Updated']);
    }
}
