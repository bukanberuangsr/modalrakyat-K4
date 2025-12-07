@extends('master')

@section('title', 'Login')

@section('content')

<div class="auth-container">
    <div class="auth-card">

        <h2 class="auth-title">Masuk</h2>
        <p class="auth-subtitle">silakan login ke akun Anda</p>

        <form id="loginForm" onsubmit="handleLogin(event)">
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

async function handleLogin(event) {
    event.preventDefault();

    const form = document.getElementById('loginForm');
    const email = form.querySelector('input[name="email"]').value;
    const password = form.querySelector('input[name="password"]').value;
    const token = form.querySelector('input[name="_token"]').value;

    try {
        const response = await fetch("/login", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": token
            },
            credentials: "same-origin",
            body: JSON.stringify({ email, password })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            alert(result.error || "Email atau password salah!");
            return;
        }

        // Login berhasil, redirect ke halaman yang sesuai
        // Simpan info user & token ke localStorage agar dashboard (yang menggunakan JWT client-side)
        if (result.user) {
            try {
                localStorage.setItem('user', JSON.stringify(result.user));
            } catch (e) { console.warn('Could not save user to localStorage', e); }
        }

        // Jika server mengembalikan token (JWT), simpan. Jika tidak, simpan placeholder supaya front-end tidak auto-redirect.
        const tokenToStore = result.token ? result.token : 'session';
        try { localStorage.setItem('token', tokenToStore); } catch (e) { console.warn('Could not save token', e); }

        console.log("Login success, redirecting to:", result.redirect_url);
        window.location.href = result.redirect_url;
    } catch (error) {
        console.error('Login error:', error);
        alert('Terjadi kesalahan saat login');
    }
}
</script>

@endsection
