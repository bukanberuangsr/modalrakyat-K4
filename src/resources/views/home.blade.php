@extends('master')

@section('title', 'Dashboard User | ModalRakyat')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Sidebar User -->
        @include('sidebar-user')

        <!-- Content -->
        <div class="main-content">

            <h1 class="page-title">Dashboard User</h1>
            <p class="page-desc">Unggah dokumen Anda untuk proses verifikasi KYC.</p>

            <!-- Upload Card -->
            <div class="cards">
                <div class="card" style="grid-column: span 2;">
                    <h3>Upload Dokumen</h3>
                    <p class="page-desc">Format PDF, JPG, atau PNG.</p>

                    <form action="{{ route('upload.document') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" class="file-input" required>
                        <button type="submit" class="btn-upload" style="margin-top:12px;">
                            Upload
                        </button>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <h2>Riwayat Upload Dokumen</h2>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($uploads as $u)
                            <tr>
                                <td>{{ $u->file_name }}</td>

                                <td>
                                    @if($u->status == 'pending')
                                        <span class="badge pending">Pending</span>
                                    @elseif($u->status == 'verified')
                                        <span class="badge success">Verified</span>
                                    @else
                                        <span class="badge danger">Rejected</span>
                                    @endif
                                </td>

                                <td>{{ $u->created_at->format('d M Y') }}</td>

                                <td>
                                    <a href="/user/file/{{ $u->id }}" class="btn-download">Download</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada dokumen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@endsection
