@extends('master')

@section('title', 'home')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Judul -->
        <h1 class="page-title">Dashboard User</h1>
        <p class="page-desc">Unggah dokumen Anda untuk proses verifikasi KYC.</p>

        <!-- Upload Dokumen -->
        <div class="upload-section">
            <div class="upload-card">
                <h3>Upload Dokumen</h3>
                <p class="upload-desc">Pilih dokumen (PDF, JPG, PNG).</p>

                <form action="/upload" method="POST" enctype="multipart/form-data">
                    @csrf

                    <label class="file-label">
                        <input type="file" name="file" required>
                    </label>

                    <button class="btn-upload">Upload Sekarang</button>
                </form>
            </div>
        </div>

        <!-- Riwayat Dokumen -->
        <div class="table-container">
            <h2>Riwayat Upload Dokumen</h2>

            <table class="table">
                <thead>
                    <tr>
                        <th>Nama File</th>
                        <th>Status</th>
                        <th>Tanggal Upload</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @if(isset($files) && count($files) > 0)
                        @foreach($files as $file)
                            <tr>
                                <td>{{ $file->original_name }}</td>

                                <td>
                                    @if($file->status == 'pending')
                                        <span class="badge pending">Pending</span>
                                    @elseif($file->status == 'verified')
                                        <span class="badge success">Verified</span>
                                    @else
                                        <span class="badge danger">Rejected</span>
                                    @endif
                                </td>

                                <td>{{ $file->created_at->format('d M Y') }}</td>

                                <td>
                                    <a href="/download/{{ $file->id }}" class="btn-download">Download</a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 18px; color:#bbb;">
                                Belum ada dokumen yang diupload.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
