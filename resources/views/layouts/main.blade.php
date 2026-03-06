<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Underground Psy')</title>
    <link rel="stylesheet" href="/css/style.css">
    @livewireStyles
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ route('home') }}" class="logo">Underground Psy</a>

                <nav class="nav">
                    <a href="{{ route('psychologists.index') }}" class="nav-link">Психологи</a>
                    <a href="{{ route('triads.slots.index') }}" class="nav-link">Тройки</a>
                    <a href="{{ route('about') }}" class="nav-link">О проекте</a>
                    @auth
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="nav-link nav-link--admin">Админ</a>
                        @endif
                        <a href="{{ route('dashboard') }}" class="nav-link">Личный кабинет</a>
                        @livewire('notification-bell')
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline">Выйти</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">Войти</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Регистрация</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-error"><p>{{ session('error') }}</p></div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <main class="main">
        @yield('content')
    </main>

    @livewireScripts
    @stack('scripts')

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Underground Psy. Некоммерческая платформа.</p>
        </div>
    </footer>
</body>
</html>
