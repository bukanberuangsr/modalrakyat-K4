@extends('master')

@section('title', 'Daftar Akun ModalRakyat')

@section('content')
<div class="auth-container">

    <div class="auth-card">

        <h2 class="auth-title">Daftar</h2>
        <p class="auth-subtitle">Buat akun ModalRakyat Anda</p>

        <div id="alert-box"></div>

        <form id="authForm">
            @csrf

            <label for="name">Nama</label>
            <input type="text" name="name" placeholder="Masukkan nama" required>

            <label for="email">Email</label>
            <input type="email" name="email" placeholder="Masukkan email" required>

            <label for="password">Kata sandi</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
                <img src="{{ asset('icons/eye.svg') }}" class="toggle-password" id="toggleIcon" onclick="togglePassword()">
            </div>

            <button type="submit" class="btn-auth-submit">Daftar</button>
        </form>

        <p class="already">Sudah memiliki akun?
            <a href="{{ route('login') }}">Masuk</a>
        </p>
    </div>
</div>

<script>
document.getElementById("authForm").addEventListener("submit", async function(e) {
    e.preventDefault();
    register();

    const formData = new FormData(this);
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const response = await fetch("{{ route('register.submit') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": csrf,
            "Accept": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
    });

    const result = await response.json();
    const alertBox = document.getElementById("alert-box");

    if (response.ok) {
        alertBox.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
        this.reset();
    } else {
        alertBox.innerHTML = `<div class="alert alert-danger">${result.error || result.message}</div>`;
    }
});

function togglePassword() {
    const pwd = document.getElementById("password");
    const icon = document.getElementById("toggleIcon");

    const isHidden = pwd.type === "password";
    pwd.type = isHidden ? "text" : "password";

    icon.src = isHidden
        ? "{{ asset('icons/eye-off.svg') }}"
        : "{{ asset('icons/eye.svg') }}";
}

async function register() {
    const form = document.getElementById("authForm");
    const alertBox = document.getElementById("alert-box");
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const formData = new FormData(form);

    try {
        const response = await fetch("{{ route('register.submit') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrf },
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            alertBox.innerHTML = `
                <div class="alert alert-success">
                    ${result.message}
                </div>
            `;

            // reset form
            form.reset();

        } else {
            alertBox.innerHTML = `
                <div class="alert alert-danger">
                    ${result.error || result.message}
                </div>
            `;
        }

    } catch (err) {
        alertBox.innerHTML = `
            <div class="alert alert-danger">
                Terjadi kesalahan pada server.
            </div>
        `;
    }
}
</script>
@endsection
