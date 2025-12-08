<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;
use App\Models\User;

class AdminController extends Controller
{
    // Ambil File sebagai admin
    public function getFile($filename)
    {
        if (!Storage::disk('s3')->exists($filename)) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        $cmd = Storage::disk('s3')->getDriver()->getAdapter()->getClient()->getCommand(
            'GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $filename
            ]
        );

        $ps = Storage::disk('s3')->getDriver()->getAdapter()->getClient()
            ->createPresignedRequest($cmd, '+1 minute');

        return response()->json([
            'download_url' => (string) $ps->getUri()
        ]);
    }

    // Melihat daftar upload dari user
    public function listUploads()
    {
        $uploads = DB::table('uploads')
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->select(
                'uploads.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('uploads.created_at', 'desc')
            ->get();
        return response()->json($uploads);
    }

    // Melihat upload spesifik dari user
    public function viewUploads($id)
    {
        $upload = DB::table('uploads')
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->where('uploads.id', $id)
            ->select(
                'uploads.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->first();

        if (!$upload) {
            return response()->json(['error'=>'Data tidak ditemukan'], 404);
        }

        // Buat presigned URL (bungkus dengan try/catch supaya aplikasi tidak crash
        // jika adapter/SDK S3 belum terpasang). Jika gagal, kembalikan metadata
        // dengan nama file sehingga front-end bisa menampilkan informasi.
        try {
            $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();

            $cmd = $client->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $upload->file_name
            ]);

            $presigned = $client->createPresignedRequest($cmd, '+3 minutes');

            return response()->json([
                'upload'       => $upload,
                'download_url' => (string) $presigned->getUri(),
            ]);
        } catch (\Throwable $e) {
            // Log the error for debugging, but don't expose internal traces to UI
            logger()->warning('S3 presigned generation failed: ' . $e->getMessage());

            $localAvailable = Storage::disk('public')->exists($upload->file_name);
            return response()->json([
                'upload'             => $upload,
                'download_url'       => null,
                'error_message'      => 'S3 client not available. File name: ' . $upload->file_name,
                'local_available'    => $localAvailable,
                'local_download_url' => url('/admin/uploads/' . $upload->id . '/download-proxy')
            ]);
        }
    }

    // Fallback: proxy download via HTTP to MinIO endpoint (works if bucket allows public GET)
    public function downloadProxy($id)
    {
        $upload = DB::table('uploads')
            ->where('uploads.id', $id)
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->select('uploads.*', 'users.name as user_name', 'users.email as user_email')
            ->first();

        if (!$upload) {
            return abort(404, 'Data tidak ditemukan');
        }

        // If file exists on local `public` disk, serve it directly from storage
        if (Storage::disk('public')->exists($upload->file_name)) {
            $localPath = storage_path('app/public/' . $upload->file_name);
            if (file_exists($localPath)) {
                return response()->download($localPath, basename($localPath));
            }
        }

        // Otherwise try MinIO via HTTP (object must be public)
        $endpoint = rtrim(env('AWS_ENDPOINT', ''), '/');
        $bucket = env('AWS_BUCKET');
        $key = ltrim($upload->file_name, '/');

        if (empty($endpoint) || empty($bucket) || empty($key)) {
            return abort(500, 'MinIO configuration incomplete');
        }

        $url = $endpoint . '/' . $bucket . '/' . $key;

        // Try to fetch file contents via HTTP (no SDK). This will succeed only if
        // MinIO bucket/object is accessible without S3 signing.
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'ignore_errors' => true,
                ]
            ]);

            $stream = @fopen($url, 'r', false, $context);
            if ($stream === false) {
                return abort(502, 'Unable to fetch file from MinIO.');
            }

            $contents = stream_get_contents($stream);
            fclose($stream);

            if ($contents === false || $contents === null) {
                return abort(502, 'Empty response from MinIO');
            }

            $basename = basename($key);
            $mime = null;
            if (function_exists('finfo_open')) {
                $f = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_buffer($f, $contents);
                finfo_close($f);
            }

            $headers = [
                'Content-Type' => $mime ?: 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $basename . '"'
            ];

            return response($contents, 200, $headers);
        } catch (\Throwable $e) {
            logger()->warning('Download proxy failed: ' . $e->getMessage());
            return abort(500, 'Download proxy failed');
        }
    }

    // Return HTTP headers for the object in MinIO so we can inspect Content-Type/size
    public function meta($id)
    {
        $upload = DB::table('uploads')
            ->where('uploads.id', $id)
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->select('uploads.*', 'users.name as user_name', 'users.email as user_email')
            ->first();

        if (!$upload) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $endpoint = rtrim(env('AWS_ENDPOINT', ''), '/');
        $bucket = env('AWS_BUCKET');
        $key = ltrim($upload->file_name, '/');

        if (empty($endpoint) || empty($bucket) || empty($key)) {
            return response()->json(['error' => 'MinIO configuration incomplete'], 500);
        }

        $url = $endpoint . '/' . $bucket . '/' . $key;

        try {
            // Use get_headers to fetch response headers
            $headers = @get_headers($url, 1);
            if ($headers === false) {
                return response()->json(['error' => 'Unable to fetch headers from MinIO', 'url' => $url], 502);
            }

            // Normalize headers into simple keys we care about
            $result = [
                'url' => $url,
                'http_code' => substr($headers[0] ?? '', 9, 3),
                'content_type' => $headers['Content-Type'] ?? ($headers['content-type'] ?? null),
                'content_length' => $headers['Content-Length'] ?? ($headers['content-length'] ?? null),
                'headers' => $headers,
            ];

            return response()->json($result);
        } catch (\Throwable $e) {
            logger()->warning('Meta fetch failed: ' . $e->getMessage());
            return response()->json(['error' => 'Meta fetch failed'], 500);
        }
    }

    // Verifikasi dokumen
    public function verifyUpload(Request $req, $id)
    {
        $req->validate([
            'status' => 'required|in:verified,rejected',
            'notes'  => 'nullable|string'
        ]);

        $upload = DB::table('uploads')->where('id', $id)->first();

        if (!$upload) {
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        }

        DB::table('uploads')
            ->where('id', $id)
            ->update([
                'status'      => $req->status,
                'notes'       => $req->notes,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'updated_at'  => now()
            ]);

        return response()->json([
            'message' => 'Status verifikasi diperbarui.',
            'status'  => $req->status
        ]);
    }

    // Menampilkan detail upload
    public function showUpload($id)
    {
        $upload = DB::table('uploads')
            ->leftJoin('users', 'uploads.user_id', '=', 'users.id')
            ->select('uploads.*', 'users.name as user_name')
            ->where('uploads.id', $id)
            ->first();

        if (!$upload) {
            abort(404);
        }

        return view('admin.verify', compact('upload'));
    }


    // Menampilkan Data-Data di Dashboard Admin
    public function dashboard()
    {
        $uploads = DB::table('uploads')
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->select(
                'uploads.id',
                'uploads.type',
                'uploads.status',
                'uploads.created_at',
                'users.name as user_name'
            )
            ->orderBy('uploads.created_at', 'desc')
            ->get();

        return view('dashboardAdmin', compact('uploads'));
    }

    // Mengambil Ringkasan Data dari Database
    public function stats()
    {
        return response()->json([
            'users' => DB::table('users')->count(),
            'uploads' => DB::table('uploads')->count(),
            'verified' => DB::table('uploads')->where('status', 'verified')->count()
        ]);
    }

    // Menampilkan Semua Users
    public function users()
    {
        $users = User::all();
        return view('adminUsers', compact('users'));
    }

    // Update Role Users
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,verifikator,user,supervisor'
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();

        return response()->json([
            'message' => 'Role pengguna berhasil diperbarui',
            'role' => $user->role
        ]);
    }
}
