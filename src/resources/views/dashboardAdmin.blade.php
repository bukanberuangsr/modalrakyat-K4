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
                    <h3>Dokumen Ditolak</h3>
                    <p class="card-number rejected">0</p>
                </div>

                <div class="card">
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
                                <td>{{ strtoupper($item->file_type) }}</td>

                                <td>
                                    @if ($item->status === 'pending')
                                        <span class="badge pending">Pending</span>
                                    @elseif ($item->status === 'verified')
                                        <span class="badge success">Verified</span>
                                    @else
                                        <span class="badge danger">Rejected</span>
                                    @endif
                                </td>

                                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>

                                <td>
                                    <a href="/admin/uploads/{{ $item->id }}" class="btn-action">Periksa</a>
                                    <a href="/admin/file/{{ $item->id }}" class="btn-download">Download</a>
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
    const token = localStorage.getItem("token");

    // Jika token tidak ada, kembalikan ke halaman login
    if (!token) {
        window.location.href = "/login";
        return;
    }

    // Ambil role user dari localStorage (disimpan saat login)
    const userData = localStorage.getItem("user");
    if (userData) {
        const user = JSON.parse(userData);

        // Cegah user non-admin masuk
        if (user.role !== "admin") {
            alert("Anda bukan admin!");
            window.location.href = "/home";
            return;
        }

        // Tampilkan nama admin jika tersedia elemen
        const adminName = document.getElementById("adminName");
        if (adminName) adminName.innerText = user.name;
    }
});

document.addEventListener("DOMContentLoaded", async () => {
    const token = localStorage.getItem("token");

    const res = await fetch("/api/admin/stats", {
        headers: { Authorization: `Bearer ${token}` }
    });

    const data = await res.json();

    document.querySelector(".card-number.total-users").innerText = data.total_users;
    document.querySelector(".card-number.pending").innerText = data.pending_docs;
    document.querySelector(".card-number.rejected").innerText = data.rejected_docs;
    document.querySelector(".card-number.encrypted").innerText = data.encrypted_docs;
});
</script>

@endsection
