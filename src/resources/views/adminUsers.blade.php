@extends('master')

@section('title', 'Data Pengguna | ModalRakyat')

@section('content')

<div class="dashboard-wrapper">
    <div class="dashboard-container">

        @include('sidebar')

        <div class="main-content">

            <h1 class="page-title">Manajemen User</h1>
            <p class="page-desc">Atur role dan akses pengguna sistem.</p>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td>Michael Ivan</td>
                            <td>michael@example.com</td>
                            <td><span class="badge success">Admin</span></td>
                            <td>
                                <button class="btn-action open-role">Ubah Role</button>
                            </td>
                        </tr>

                        <tr>
                            <td>Sarah Putri</td>
                            <td>sarah@example.com</td>
                            <td><span class="badge pending">User</span></td>
                            <td>
                                <button class="btn-action open-role">Ubah Role</button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


<!-- Modal Role -->
<div class="modal" id="modal-role">
    <div class="modal-content">
        <h3>Ubah Role Pengguna</h3>

        <select class="input-role">
            <option value="admin">Admin</option>
            <option value="verifikator">Verifikator</option>
            <option value="user">User</option>
            <option value="supervisor">Supervisor</option>
        </select>

        <div class="modal-action">
            <button class="btn-action">Simpan</button>
            <button class="btn-download close-modal">Batal</button>
        </div>
    </div>
</div>

@endsection
