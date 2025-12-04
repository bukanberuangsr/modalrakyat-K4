<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    return back()->with('success', 'File berhasil diupload! Lokasi: ' . $path);
}

}
