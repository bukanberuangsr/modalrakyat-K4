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
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->role == 'admin' ? 'success' : 'pending' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-action open-role" data-user="{{ $user->id }}">
                                        Ubah Role
                                    </button>
                                </td>
                            </tr>
                        @endforeach
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-role');
    const inputRole = modal.querySelector('.input-role');
    let selectedUserId = null;

    // Buka modal
    document.querySelectorAll('.open-role').forEach(btn => {
        btn.addEventListener('click', () => {
            selectedUserId = btn.dataset.user;
            modal.style.display = 'flex';
        });
    });

    // Tutup modal
    modal.querySelector('.close-modal').addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Simpan role
    modal.querySelector('.btn-action').addEventListener('click', () => {
        if (!selectedUserId) return;

        fetch(`/user/${selectedUserId}/role`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ role: inputRole.value })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            location.reload(); // Reload halaman untuk mengupdate role
        })
        .catch(err => console.error(err));
    });
});
</script>

@endsection