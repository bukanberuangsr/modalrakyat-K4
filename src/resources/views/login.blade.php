@extends('master')

@section('title', 'login')

@section('content')

    <div class="login">
        <h1>Login</h1>
        <form action="post">
            <label for="email">Email</label>
            <input type="email" name="" id="">
            <label for="password">Password</label>
            <input type="password" name="" id="">
            <input type="submit" value="">
        </form>
    </div>

@endsection
