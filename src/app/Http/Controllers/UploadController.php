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
        $uuid = Str::uuid()->toString() . '.jpg';

        $command = Storage::disk('s3')
            ->getDriver()
            ->getAdapter()
            ->getClient()
            ->getCommand(
                'PutObject', [
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => $uuid,
                    'ContentType' => 'image/jpeg'
                    ]
                );

        $presignedRequest = Storage::disk('s3')
            ->getDriver()
            ->getAdapter()
            ->getClient()
            ->createPresignedRequest($command, '+2 minute');

        return response()->json([
            'upload_url' => (string) $presignedRequest->getUri(),
            'file_name' => $uuid
        ]);
    }

    // Validasi dan simpan setelah upload
    public function upload(Request $request)
    {
        // Validasi file form biasa
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        // Simpan ke storage lokal (public/uploads)
        $path = $request->file('file')->store('uploads', 'public');

        // Simpan metadata ke tabel uploads
        try {
            $file = $request->file('file');
            $fullPath = $path; // relative path in storage (e.g. uploads/abc.png)
            $size = $file->getSize();
            $hash = hash_file('sha256', $file->getRealPath());

            $upload = Upload::create([
                'user_id' => auth('web')->id(),
                'file_name' => $fullPath,
                'file_hash' => $hash,
                'size' => $size,
                'type' => $request->input('type'),
            ]);

            // Try to push the file to MinIO (s3 disk). If successful, delete local copy.
            try {
                if (Storage::disk('public')->exists($fullPath)) {
                    $localFullPath = storage_path('app/public/' . $fullPath);
                    if (file_exists($localFullPath)) {
                        $stream = fopen($localFullPath, 'r');
                        if ($stream !== false) {
                            // Put to S3 using same key (uploads/xxx)
                            Storage::disk('s3')->put($fullPath, $stream);
                            if (is_resource($stream)) {
                                fclose($stream);
                            }

                            // Update DB record to reflect it's stored on S3 (same file_name key)
                            $upload->file_name = $fullPath;
                            $upload->save();

                            // Remove local copy to avoid duplicates
                            Storage::disk('public')->delete($fullPath);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Log s3 push failure but don't fail the request
                Log::warning('Failed to push upload to S3/MinIO: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Failed saving upload metadata: ' . $e->getMessage());
        }

        return back()->with('success', 'File berhasil diupload! Lokasi: ' . $path);
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
    }
}
