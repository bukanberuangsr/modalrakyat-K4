<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use DB;
use App\Models\Upload;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    // Presigned URL untuk upload
    public function getPresignedUrl(Request $req)
    {
        try {
            // ambil ekstensi dari request (default jpg)
            $ext = $req->input('ext', 'jpg');
            $uuid = 'uploads/' . Str::uuid()->toString() . '.' . $ext;

            $mime = match($ext) {
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                default => 'application/octet-stream',
            };

            $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();

            $command = $client->getCommand('PutObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $uuid,
                'ContentType' => $mime,
            ]);

            $presignedRequest = $client->createPresignedRequest($command, '+5 minutes');

            return response()->json([
                'upload_url' => (string) $presignedRequest->getUri(),
                'file_name' => $uuid
            ]);
        } catch (\Exception $e) {
            Log::error('Presigned URL error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Validasi dan simpan setelah upload
    public function upload(Request $request)
    {
        try {
            // Validasi file form biasa
            $request->validate([
                'file' => 'required|mimes:pdf,jpg,jpeg,png|max:2048'
            ]);

            $file = $request->file('file');
            $fileName = 'uploads/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            // Upload langsung ke S3/MinIO
            $stream = fopen($file->getRealPath(), 'r');
            Storage::disk('s3')->put($fileName, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            // Simpan metadata ke database
            $hash = hash_file('sha256', $file->getRealPath());
            
            Upload::create([
                'user_id' => auth('web')->id(),
                'file_name' => $fileName,
                'file_hash' => $hash,
                'size' => $file->getSize(),
                'type' => $request->input('type'),
            ]);

            return back()->with('success', 'File berhasil diupload!');
            
        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
            return back()->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }
    
    // User dapat melihat apa yang di Upload
    public function myUploads(Request $req)
    {
        $userId = auth('web')->id();
        $uploads = DB::table('uploads')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($uploads);
    }

    // Setelah upload ke S3 via presigned URL, panggil endpoint ini untuk mendaftarkan file
    public function validateUploadFile(Request $request)
    {
        try {
            $request->validate([
                'file_name' => 'required|string',
                'size' => 'required|integer',
                'file_hash' => 'required|string',
                'type' => 'required|string'
            ]);

            $userId = auth('web')->id();

            Log::info('validateUploadFile called by user id: ' . ($userId ?? 'null'));

            $upload = Upload::create([
                'user_id' => $userId,
                'file_name' => $request->input('file_name'),
                'file_hash' => $request->input('file_hash'),
                'size' => $request->input('size'),
                'type' => $request->input('type'),
            ]);

            return response()->json(['success' => true, 'upload_id' => $upload->id]);
            
        } catch (\Exception $e) {
            Log::error('Validate upload error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Download file untuk user
    public function downloadFile($id)
    {
        try {
            $upload = Upload::findOrFail($id);
            
            // Pastikan user hanya bisa download file miliknya sendiri
            if ($upload->user_id !== auth('web')->id()) {
                abort(403, 'Unauthorized');
            }
            
            // Download dari S3
            if (Storage::disk('s3')->exists($upload->file_name)) {
                return Storage::disk('s3')->download($upload->file_name);
            }
            
            abort(404, 'File tidak ditemukan');
            
        } catch (\Exception $e) {
            Log::error('Download error: ' . $e->getMessage());
            abort(500, 'Gagal download file');
        }
    }
}