@extends('master')

@section('title', 'Dashboard User | ModalRakyat')

@section('content')

    {{-- NAVBAR --}}
    <nav class="user-navbar">
        <div class="navbar-inner">
            <h2 class="navbar-logo">ModalRakyat</h2>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="logout-btn">Logout</button>
            </form>
        </div>
    </nav>

    {{-- WRAPPER KONTEN --}}
    <div class="dashboard-wrapper">
        
        <div class="dashboard-columns">
            <div class="left-column">
                <div class="header-section">
                    <h1 class="page-title">Dashboard User</h1>
                    <p class="page-desc">Unggah dokumen Anda untuk proses verifikasi KYC.</p>
                </div>

                <div class="card upload-card">
                    <h3>Upload Dokumen</h3>
                    <p class="sub-text">Pilih dokumen (PDF, JPG, PNG).</p>

                    <form action="{{ route('upload.document') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="file" id="file" class="file-input" required>
                        </div>
                        <button type="submit" class="btn-primary">Upload Sekarang</button>
                    </form>
                </div>
            </div>

            <div class="right-column">
                <div class="card history-card">
                    <h3>Riwayat Upload Dokumen</h3>
                    
                    <div class="table-responsive">
                        <table class="custom-table">
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
                                            @if($u->status == 'pending') <span class="badge pending">Pending</span>
                                            @elseif($u->status == 'verified') <span class="badge success">Verified</span>
                                            @else <span class="badge danger">Rejected</span> @endif
                                        </td>
                                        <td>{{ $u->created_at->format('d M Y') }}</td>
                                        <td><a href="#" class="link-download">Download</a></td>
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
    </div>

@endsection