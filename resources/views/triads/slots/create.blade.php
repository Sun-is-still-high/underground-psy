@extends('layouts.main')

@section('title', 'Создать слот — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Создать слот</h1>
            <p class="page-subtitle">Выберите задание, свою роль и время</p>
        </div>
        <a href="{{ route('triads.slots.index') }}" class="btn btn-outline">← К ленте</a>
    </div>

    <div class="form-card card">
        <form method="POST" action="{{ route('triads.slots.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Задание</label>
                <select name="task_id" class="form-control" required id="task-select">
                    <option value="">— Выберите задание —</option>
                    @foreach($tasks as $task)
                        <option value="{{ $task->id }}"
                                data-duration="{{ $task->duration_minutes }}"
                                {{ old('task_id') == $task->id ? 'selected' : '' }}>
                            {{ $task->title }} ({{ $task->duration_minutes }} мин)
                        </option>
                    @endforeach
                </select>
                <p class="form-hint">Длительность сессии определяется заданием</p>
            </div>

            <div class="form-group">
                <label class="form-label">Ваша роль</label>
                <div class="role-select-group">
                    @foreach(['therapist' => 'Терапевт', 'client' => 'Клиент', 'observer' => 'Наблюдатель'] as $value => $label)
                        <label class="role-select-option">
                            <input type="radio" name="role" value="{{ $value }}"
                                   {{ old('role') === $value ? 'checked' : '' }} required>
                            <span class="role-badge role-badge--{{ $value }}">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Дата и время начала</label>
                <input type="datetime-local" name="starts_at"
                       value="{{ old('starts_at') }}"
                       class="form-control" style="max-width: 280px;"
                       min="{{ now()->addHour()->format('Y-m-d\TH:i') }}" required>
                <p class="form-hint">Минимум — через час от текущего времени</p>
            </div>

            <div class="form-group">
                <label class="form-label">Видимость</label>
                <div style="display: flex; gap: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="radio" name="visibility" value="public"
                               {{ old('visibility', 'public') === 'public' ? 'checked' : '' }}>
                        Публичный (виден в ленте)
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="radio" name="visibility" value="private"
                               {{ old('visibility') === 'private' ? 'checked' : '' }}>
                        Приватный (только по приглашению)
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="blind_mode" value="1"
                           {{ old('blind_mode') ? 'checked' : '' }}>
                    <strong>Слепой режим</strong> — терапевт не видит инструкцию клиента
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Создать слот</button>
                <a href="{{ route('triads.slots.index') }}" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
