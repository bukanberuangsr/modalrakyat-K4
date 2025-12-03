<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;

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

        // Buat presigned URL
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
}
