<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; 
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

    // Melihat upload spesifik dari user (API endpoint untuk AJAX)
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

        try {
            $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();

            // Deteksi tipe file
            $extension = strtolower(pathinfo($upload->file_name, PATHINFO_EXTENSION));
            $contentType = match($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
                default => 'application/octet-stream',
            };

            $cmd = $client->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $upload->file_name,
                'ResponseContentDisposition' => 'inline', // Inline untuk preview
                'ResponseContentType' => $contentType
            ]);

            $presigned = $client->createPresignedRequest($cmd, '+15 minutes'); // Lebih lama untuk preview

            return response()->json([
                'upload' => $upload,
                'download_url' => (string) $presigned->getUri(),
            ]);
            
        } catch (\Throwable $e) {
            Log::error('Presigned URL generation failed for upload ' . $id . ': ' . $e->getMessage());

            return response()->json([
                'upload' => $upload,
                'download_url' => null,
                'error' => 'Gagal generate preview URL'
            ]);
        }
    }

    // Download dokumen via presigned URL
    public function downloadProxy($id)
    {
        try {
            $upload = DB::table('uploads')->where('id', $id)->first();

            if (!$upload) {
                abort(404, 'Data tidak ditemukan');
            }

            // Cek apakah file ada di S3
            if (!Storage::disk('s3')->exists($upload->file_name)) {
                abort(404, 'File tidak ditemukan di storage');
            }

            // Generate presigned URL untuk download dari S3
            $client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();
            
            $extension = strtolower(pathinfo($upload->file_name, PATHINFO_EXTENSION));
            $contentType = match($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'pdf' => 'application/pdf',
                default => 'application/octet-stream',
            };
            
            $cmd = $client->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $upload->file_name,
                'ResponseContentType' => $contentType,
                'ResponseContentDisposition' => 'attachment; filename="' . basename($upload->file_name) . '"'
            ]);
            
            $presignedRequest = $client->createPresignedRequest($cmd, '+10 minutes');
            
            // Redirect ke presigned URL
            return redirect((string) $presignedRequest->getUri());
            
        } catch (\Throwable $e) {
            Log::error('Download proxy failed for ID ' . $id . ': ' . $e->getMessage());
            abort(500, 'Download gagal: ' . $e->getMessage());
        }
    }

    // Return HTTP headers for the object in MinIO
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
            $headers = @get_headers($url, 1);
            if ($headers === false) {
                return response()->json(['error' => 'Unable to fetch headers from MinIO', 'url' => $url], 502);
            }

            $result = [
                'url' => $url,
                'http_code' => substr($headers[0] ?? '', 9, 3),
                'content_type' => $headers['Content-Type'] ?? ($headers['content-type'] ?? null),
                'content_length' => $headers['Content-Length'] ?? ($headers['content-length'] ?? null),
                'headers' => $headers,
            ];

            return response()->json($result);
        } catch (\Throwable $e) {
            Log::warning('Meta fetch failed: ' . $e->getMessage());
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
            'message' => 'Status verifikasi berhasil diperbarui',
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


    // Menampilkan view detail upload (halaman HTML)
    public function detailUpload($id)
    {
        // Cek apakah upload ada
        $upload = DB::table('uploads')->where('id', $id)->first();
        
        if (!$upload) {
            abort(404, 'Dokumen tidak ditemukan');
        }
        
        // Render halaman HTML
        return view('adminDetail', ['uploadId' => $id]);
    }

    // Menampilkan Dashboard Admin (halaman HTML)
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

    // API: Mengambil statistik untuk dashboard (JSON)
    public function stats()
    {
        return response()->json([
            'total_users' => DB::table('users')->count(),
            'pending_docs' => DB::table('uploads')->where('status', 'pending')->count(),
            'verified_docs' => DB::table('uploads')->where('status', 'verified')->count(),
            'rejected_docs' => DB::table('uploads')->where('status', 'rejected')->count(),
            'encrypted_docs' => DB::table('uploads')->count()
        ]);
    }

    // Menampilkan halaman user management (halaman HTML)
    public function users()
    {
        $users = User::all();
        return view('adminUsers', compact('users'));
    }

    // Update role user (API endpoint)
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