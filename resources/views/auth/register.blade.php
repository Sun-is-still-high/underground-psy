@extends('layouts.main')

@section('title', 'Регистрация - Underground Psy')

@section('content')
<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Регистрация</h1>

            <form action="{{ route('register') }}" method="POST" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="name">Имя</label>
                    <input type="text" id="name" name="name" class="form-control"
                        value="{{ old('name') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="8">
                    <small>Минимум 8 символов</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Подтверждение пароля</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="role">Я хочу зарегистрироваться как:</label>
                    <select id="role" name="role" class="form-control">
                        <option value="CLIENT" {{ old('role', 'CLIENT') === 'CLIENT' ? 'selected' : '' }}>Клиент</option>
                        <option value="PSYCHOLOGIST" {{ old('role') === 'PSYCHOLOGIST' ? 'selected' : '' }}>Психолог</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
            </form>

            <div class="auth-footer">
                <p>Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
