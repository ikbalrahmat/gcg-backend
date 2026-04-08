<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssessmentController extends Controller
{
    public function index()
    {
        $assessments = Assessment::with('members')->orderBy('created_at', 'desc')->get();
        return response()->json($assessments->map(function ($a) {
            return [
                'id' => $a->id,
                'year' => $a->year,
                'tb' => $a->tb,
                'noSt' => $a->no_st,
                'pt' => $a->pt,
                'kt' => $a->kt,
                'status' => $a->status,
                'finalReportUrl' => $a->final_report_url,
                'finalReportName' => $a->final_report_name,
                'createdAt' => $a->created_at->toISOString(),
                'createdBy' => $a->created_by,
                'data' => $a->data ?? new \stdClass(),
                'members' => $a->members->map(fn($m) => ['name' => $m->name, 'aspectId' => $m->aspectId])
            ];
        }));
    }

    public function store(Request $request)
    {
        $assessment = Assessment::create([
            'id' => $request->id,
            'year' => $request->year,
            'tb' => $request->tb,
            'no_st' => $request->noSt,
            'pt' => $request->pt,
            'kt' => $request->kt,
            'status' => 'Draft',
            'created_by' => $request->user()->name,
            'data' => $request->has('data') ? $request->data : new \stdClass(),
        ]);

        if ($request->has('members')) {
            foreach ($request->members as $member) {
                $assessment->members()->create(['name' => $member['name'], 'aspectId' => $member['aspectId']]);
            }
        }
        return response()->json(['message' => 'Berhasil dibuat', 'data' => $assessment]);
    }

    public function update(Request $request, $id)
    {
        $assessment = Assessment::findOrFail($id);

        $assessment->update([
            'year' => $request->year,
            'tb' => $request->tb,
            'no_st' => $request->noSt,
            'pt' => $request->pt,
            'kt' => $request->kt,
        ]);

        if ($request->has('members')) {
            $assessment->members()->delete();
            foreach ($request->members as $member) {
                $assessment->members()->create(['name' => $member['name'], 'aspectId' => $member['aspectId']]);
            }
        }
        return response()->json(['message' => 'Berhasil diupdate']);
    }

    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);

        // Hapus juga file fisiknya dari storage jika assessment dihapus
        if ($assessment->final_report_url) {
            $path = str_replace('/storage/', '', $assessment->final_report_url);
            Storage::disk('public')->delete($path);
        }

        $assessment->delete();
        return response()->json(['message' => 'Berhasil dihapus']);
    }

    public function updateData(Request $request, $id)
    {
        $assessment = Assessment::findOrFail($id);
        if ($request->has('data')) { $assessment->data = $request->data; }
        if ($request->has('status')) { $assessment->status = $request->status; }
        $assessment->save();

        return response()->json(['message' => 'Kertas Kerja Tersimpan']);
    }

    // Endpoint khusus untuk upload Laporan Final menggunakan FormData
    public function uploadFinalReport(Request $request, $id)
    {
        $assessment = Assessment::findOrFail($id);

        // Validasi file PDF maksimal 10MB
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Bikin nama file unik biar nggak bentrok
            $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());

            // Simpan file ke folder storage/app/public/reports
            $path = $file->storeAs('reports', $fileName, 'public');

            // Update database dengan URL file-nya saja
            $assessment->update([
                'final_report_url' => '/storage/' . $path,
                'final_report_name' => $file->getClientOriginalName(),
            ]);

            return response()->json([
                'message' => 'Laporan Final berhasil diunggah!',
                'url' => '/storage/' . $path
            ]);
        }

        return response()->json(['message' => 'Data file tidak ditemukan'], 400);
    }
}
