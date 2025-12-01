@extends('master')

@section('title', 'Login')

@section('content')

<div class="register-container">
    <div class="register-card">

        <h2 class="register-title">Masuk</h2>
        <p class="register-subtitle">silakan login ke akun Anda</p>

        <form action="/login" method="POST">
            @csrf

            <label>Email</label>
            <input type="email" name="email" placeholder="Masukkan email" required>

            <label>Kata sandi</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
                <img src="{{ asset('icons/eye.svg') }}" class="toggle-password" id="toggleIcon" onclick="togglePassword()">
            </div>

            <button type="submit" class="btn-register">Masuk</button>

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
</script>
@endsection
