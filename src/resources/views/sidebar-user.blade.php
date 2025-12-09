<div class="sidebar">
    <h2 class="logo">ModalRakyat</h2>

    <ul class="menu">
        <a href="/home" class="menu-item">
            <li class="{{ Route::is('home') ? 'active' : '' }}">Dashboard</li>
        </a>

        <a href="/profile" class="menu-item">
            <li class="{{ Route::is('profile') ? 'active' : '' }}">Profil Saya</li>
        </a>
    </ul>

    <button id="btnLogout" class="btn-logout">Keluar</button>
</div>

<script>
document.getElementById("btnLogout").addEventListener("click", async () => {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    try {
        await fetch("/logout", {
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        });
    } catch (e) {
        console.warn("Logout error:", e);
    }

    // Hapus data user dan token
    localStorage.removeItem("token");
    localStorage.removeItem("user");

    // Mengarahkan ke login
    window.location.href = "/login";
});
</script>
