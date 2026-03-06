@extends('layouts.main')

@section('title', 'Вход - Underground Psy')

@section('content')
<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Вход в систему</h1>

            <form action="{{ route('login') }}" method="POST" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Войти</button>
            </form>

            <div class="auth-divider" style="text-align:center;margin:1.25rem 0;color:#9ca3af;font-size:0.85rem;">
                — или войдите как психолог через —
            </div>

            <div style="display:flex;gap:12px;justify-content:center;margin-bottom:1rem;">
                <a href="{{ route('oauth.redirect', 'vkontakte') }}" class="btn btn-outline" style="flex:1;text-align:center;">
                    ВКонтакте
                </a>
                <a href="{{ route('oauth.redirect', 'yandex') }}" class="btn btn-outline" style="flex:1;text-align:center;">
                    Яндекс
                </a>
            </div>

            <div class="auth-footer">
                <p>Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
