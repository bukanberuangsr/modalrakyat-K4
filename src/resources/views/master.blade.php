<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'ModalRakyat')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">


    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body>
    <div class="container">
        @yield('content')
    </div>

    <script>
        const modal = document.getElementById('modal-role');

        document.querySelectorAll('.open-role').forEach(btn=>{
            btn.addEventListener('click', ()=> {
                modal.style.display = 'flex';
            });
        });

        document.querySelector('.close-modal').addEventListener('click', ()=> {
            modal.style.display = 'none';
        });
    </script>
</body>
</html>