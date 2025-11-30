@extends('master')

@section('title', 'Dashboard Admin | ModalRakyat')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">

        <!-- Sidebar -->
        <div class="sidebar">
            <h2 class="logo">ModalRakyat</h2>

            <ul class="menu">
                <li class="active">Dashboard</li>
                <li>Verifikasi Dokumen</li>
                <li>Data Pengguna</li>
                <li>Audit Log</li>
                <li>Pengaturan</li>
            </ul>

            <button class="btn-logout">Keluar</button>
        </div>

        <!-- Content -->
        <div class="main-content">

            <h1 class="page-title">Dashboard Admin</h1>
            <p class="page-desc">Pantau status unggahan dokumen KYC dan keamanan sistem.</p>

            <!-- Cards -->
            <div class="cards">
                <div class="card">
                    <h3>Pengguna Terdaftar</h3>
                    <p class="card-number">1.284</p>
                </div>

                <div class="card">
                    <h3>Menunggu Verifikasi</h3>
                    <p class="card-number pending">37</p>
                </div>

                <div class="card">
                    <h3>Dokumen Ditolak</h3>
                    <p class="card-number rejected">5</p>
                </div>

                <div class="card">
                    <h3>Total Dokumen Terenkripsi</h3>
                    <p class="card-number encrypted">1.279</p>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <h2>Daftar Dokumen KYC</h2>

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
                        <tr>
                            <td>Michael Ivan</td>
                            <td>KTP</td>
                            <td><span class="badge pending">Pending</span></td>
                            <td>30 Nov 2025</td>
                            <td><button class="btn-action">Periksa</button></td>
                        </tr>
                        <tr>
                            <td>Rizki Andika</td>
                            <td>Slip Gaji</td>
                            <td><span class="badge success">Verified</span></td>
                            <td>29 Nov 2025</td>
                            <td><button class="btn-action">Detail</button></td>
                        </tr>
                        <tr>
                            <td>Sarah Putri</td>
                            <td>KTP</td>
                            <td><span class="badge danger">Rejected</span></td>
                            <td>28 Nov 2025</td>
                            <td><button class="btn-action">Ulangi</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
