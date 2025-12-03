<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use DB;

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
    public function validateUploadFile(Request $req)
    {
        $fileName = $req->file_name;

        if (!Storage::disk('s3')->exists($fileName)) {
            return response()->json(['error'=>'File tidak ditemukan']);
        }

        $stream = Storage::disk('s3')->readStream($fileName);
        $hash = hash_file('sha256', stream_get_meta_data($stream)['uri']);
        $size = Storage::disk('s3')->size($fileName);

        // Simpan ke database
        DB::table('uploads')->insert([
            'user_id'   => $req->user()->id,
            'file_name' => $fileName,
            'file_hash' => $hash,
            'size'      => $size,
            'type'      => $req->type ?? 'unknown',
            'status'    => 'pending', // default KYC status
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['status' => 'validated']);
    }

    // User dapat melihat apa yang di Upload
    public function myUploads(Request $req)
    {
        $uploads = DB::table('uploads')
            ->where('user_id', $req->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($uploads);
    }
}
