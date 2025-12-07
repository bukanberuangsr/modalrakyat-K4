@extends('master')

@section('title', 'Login')

@section('content')

<div class="auth-container">
    <div class="auth-card">

        <h2 class="auth-title">Masuk</h2>
        <p class="auth-subtitle">silakan login ke akun Anda</p>

        <form onsubmit="login(event)">
            @csrf

            <label>Email</label>
            <input type="email" name="email" placeholder="Masukkan email" required>

            <label>Kata sandi</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
                <img src="{{ asset('icons/eye.svg') }}" class="toggle-password" id="toggleIcon" onclick="togglePassword()">
            </div>

            <button type="submit" id="btnLogin" class="btn-auth-submit">Masuk</button>

            <div class="already">
                Belum punya akun? <a href="/register">Daftar</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword() {
    const pwd = document.getElementById("password");
    const icon = document.getElementById("toggleIcon");

    const isHidden = pwd.type === "password";
    pwd.type = isHidden ? "text" : "password";

    icon.src = isHidden
        ? "{{ asset('icons/eye-off.svg') }}"
        : "{{ asset('icons/eye.svg') }}";
}

async function login(event) {
    event.preventDefault();

    const email = document.querySelector('input[name="email"]').value;
    const password = document.querySelector('input[name="password"]').value;

    const response = await fetch("/login", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        credentials: "same-origin",
        body: JSON.stringify({ email, password })
    });

    const result = await response.json();

    if (!response.ok) {
        alert("Email atau password salah!");
        return;
    }

    // Simpan token JWT
    localStorage.setItem("token", result.token);

    // Redirect jika admin
    if (result.user.role === "admin") {
        window.location.href = "/dashboard/admin";
    } else {
        window.location.href = "/home";
    }
}
</script>
@endsection
