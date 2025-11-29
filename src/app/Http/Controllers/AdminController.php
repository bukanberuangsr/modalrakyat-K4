<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
}
