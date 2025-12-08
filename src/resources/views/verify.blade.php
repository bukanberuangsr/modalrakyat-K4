@extends('master')

@section('title', 'Verifikasi Dokumen')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        @include('sidebar')

        <div class="main-content">
            <h1>Verifikasi Dokumen Pengguna</h1>

            <p><strong>Nama Pengguna:</strong> {{ $upload->user_name }}</p>
            <p><strong>Jenis Dokumen:</strong> {{ strtoupper($upload->type) }}</p>
            <p><strong>Nama File:</strong> {{ $upload->file_name }}</p>

            <a href="/admin/file/{{ $upload->id }}" class="btn-download" target="_blank">
                Preview / Download File
            </a>

            <hr>

            <form action="{{ route('admin.verify.upload', $upload->id) }}" method="POST">
                @csrf

                <label>Status:</label>
                <select name="status" required>
                    <option value="verified">Verifikasi</option>
                    <option value="rejected">Tolak</option>
                </select>

                <label>Catatan (opsional):</label>
                <textarea name="notes" rows="3" placeholder="Alasan penolakan (jika ditolak)"></textarea>

                <button type="submit" class="btn-action" style="margin-top:10px;">
                    Simpan Verifikasi
                </button>
            </form>

        </div>
    </div>
</div>

@endsection
