@extends('layouts.main')

@section('title', 'Настройки — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Настройки</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Часовой пояс --}}
    <div class="settings-section">
        <h2>Часовой пояс</h2>
        <p class="text-muted">Текущий: <strong>{{ $timezones[$user->timezone] ?? $user->timezone ?? 'не задан' }}</strong></p>

        <form action="{{ route('settings.timezone') }}" method="POST" class="settings-form">
            @csrf
            <div class="form-group">
                <select name="timezone" class="form-control" style="max-width:320px;">
                    @foreach ($timezones as $value => $label)
                        <option value="{{ $value }}" {{ ($user->timezone ?? 'Europe/Moscow') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>

    {{-- Пол --}}
    <div class="settings-section">
        <h2>Пол</h2>
        <form action="{{ route('settings.gender') }}" method="POST" class="settings-form">
            @csrf
            <div class="form-group">
                <select name="gender" class="form-control" style="max-width:200px;">
                    <option value="">— Не указан —</option>
                    @foreach ($genders as $value => $label)
                        <option value="{{ $value }}" {{ $user->gender === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        </form>
    </div>
</div>
@endsection
