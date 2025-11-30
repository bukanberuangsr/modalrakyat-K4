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
                <input type="password" name="password" placeholder="Masukkan kata sandi" required>
            </div>

            <button type="submit" class="btn-register">Masuk</button>

            <div class="already">
                Belum punya akun? <a href="/register">Daftar</a>
            </div>
        </form>

    </div>
</div>

@endsection
