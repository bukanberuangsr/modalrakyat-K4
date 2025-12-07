<div class="sidebar">
    <h2 class="logo">ModalRakyat</h2>

    <ul class="menu">
        <a href="/dashboard/admin" class="menu-item">
            <li class="{{ Route::is('dashboard') ? 'active' : '' }}">Dashboard</li>
        </a>

        <a href="{{ route('admin.users') }}" class="menu-item">
            <li class="{{ Route::is('admin.users') ? 'active' : '' }}">Data Pengguna</li>
        </a>
    </ul>

    <button id="btnLogout" class="btn-logout">Keluar</button>
</div>

<script>
document.getElementById("btnLogout").addEventListener("click", async () => {
    try {
        await fetch('/logout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin'
        });
    } catch (e) {
        console.warn('Logout error:', e);
    }

    // Hapus data lokal jika ada
    try { localStorage.removeItem('token'); localStorage.removeItem('user'); } catch(e){}

    // Mengarahkan ke login
    window.location.href = '/login';
});
</script>
