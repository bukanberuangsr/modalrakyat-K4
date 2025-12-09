@extends('master')

@section('title', 'Dashboard Admin | ModalRakyat')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Sidebar -->
        @include('sidebar')

        <!-- Content -->
        <div class="main-content">

            <h1 class="page-title">Dashboard Admin</h1>
            <p class="page-desc">Status unggahan dokumen KYC.</p>

            <!-- Cards -->
            <div class="cards">
                <div class="card">
                    <h3>Pengguna Terdaftar</h3>
                    <p class="card-number total-users">0</p>
                </div>

                <div class="card">
                    <h3>Dokumen Menunggu Verifikasi</h3>
                    <p class="card-number pending">0</p>
                </div>

                <div class="card">
                    <h3>Dokumen Terverifikasi</h3>
                    <p class="card-number verified">0</p>
                </div>

                <div class="card">
                    <h3>Dokumen Ditolak</h3>
                    <p class="card-number rejected">0</p>
                </div>
            </div>

            <!-- Card Terenkripsi Tengah -->
            <div class="card-center-wrapper">
                <div class="card card-encrypted">
                    <h3>Total Dokumen Terenkripsi</h3>
                    <p class="card-number encrypted">0</p>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <h2>Daftar Dokumen Know Your Customer</h2>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Pengguna</th>
                            <th>Jenis Dokumen</th>
                            <th>Status</th>
                            <th>Tanggal Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($uploads as $item)
                            <tr>
                                <td>{{ $item->user_name }}</td>
                                <td>{{ strtoupper($item->type) }}</td>

                                <td>
                                    @if ($item->status === 'pending')
                                        <span class="badge pending">Pending</span>
                                    @elseif ($item->status === 'verified')
                                        <span class="badge success">Verified</span>
                                    @else
                                        <span class="badge danger">Rejected</span>
                                    @endif
                                </td>

                                <td>{{ \Carbon\Carbon::parse($item->created_at)->timezone('Asia/Jakarta')->format('d M Y') }}</td>

                                <td>
                                    <a href="/admin/uploads/{{ $item->id }}" class="btn-action">Periksa</a>
                                    <a href="/admin/uploads/{{ $item->id }}/download" class="btn-download" target="_blank">Download</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const adminName = document.getElementById("adminName");
    @if(auth('web')->check())
        if (adminName) adminName.innerText = "{{ addslashes(auth('web')->user()->name) }}";
    @endif

    // Fetch dashboard stats
    (async function(){
        try {
            const res = await fetch('/api/admin/stats', { credentials: 'same-origin' });
            if (!res.ok) {
                console.warn('Failed to fetch admin stats', res.status);
                return;
            }
            const data = await res.json();

            document.querySelector('.card-number.total-users').innerText = data.total_users;
            document.querySelector('.card-number.pending').innerText = data.pending_docs;
            document.querySelector('.card-number.verified').innerText = data.verified_docs || 0;
            document.querySelector('.card-number.rejected').innerText = data.rejected_docs;
            document.querySelector('.card-number.encrypted').innerText = data.encrypted_docs;
        } catch (e) {
            console.warn('Error fetching admin stats', e);
        }
    })();
});
</script>

@endsection