@extends('master')

@section('title', 'Profil Saya | ModalRakyat')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">

        @include('sidebar-user')

        {{-- Konten --}}
        <div class="main-content">
            <h1 class="page-title">Profil Saya</h1>
            <p class="page-desc">Informasi akun yang Anda gunakan.</p>

            <div class="card" style="max-width: 500px;">
                <h3>Informasi Akun</h3>

                <div style="margin-top: 20px;">
                    <p><strong>Nama</strong></p>
                    <p style="color:#bbbbbb;">{{ auth()->user()->name }}</p>
                </div>

                <div style="margin-top: 16px;">
                    <p><strong>Email</strong></p>
                    <p style="color:#bbbbbb;">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById("btnLogout").addEventListener("click", async () => {
    const token = localStorage.getItem("token");

    try {
        await fetch("/logout", {
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            }
        });
    } catch (e) {}

    localStorage.removeItem("token");
    localStorage.removeItem("user");
    window.location.href = "/login";
});
</script>
@endsection
