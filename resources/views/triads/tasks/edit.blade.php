@extends('layouts.main')

@section('title', 'Редактировать задание — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Исправить задание</h1>
            <p class="page-subtitle">После сохранения задание будет повторно отправлено на модерацию</p>
        </div>
        <a href="{{ route('triads.tasks.my') }}" class="btn btn-outline">← Мои задания</a>
    </div>

    @if($task->moderation_comment)
        <div class="alert alert-error">
            <strong>Причина отклонения:</strong> {{ $task->moderation_comment }}
        </div>
    @endif

    <div class="form-card card">
        <form method="POST" action="{{ route('triads.tasks.update', $task) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Название задания</label>
                <input type="text" name="title" value="{{ old('title', $task->title) }}"
                       class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Описание кейса</label>
                <textarea name="description" class="form-control" rows="4" required>{{ old('description', $task->description) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Длительность сессии (минут)</label>
                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $task->duration_minutes) }}"
                       class="form-control" style="max-width: 120px;" min="10" max="180" required>
            </div>

            <hr class="form-divider">
            <p class="form-section-title">Инструкции по ролям</p>

            <div class="form-group">
                <label class="form-label">
                    <span class="role-badge role-badge--therapist">Терапевт</span>
                    Инструкция для терапевта
                </label>
                <textarea name="instruction_therapist" class="form-control" rows="4" required>{{ old('instruction_therapist', $task->instruction_therapist) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <span class="role-badge role-badge--client">Клиент</span>
                    Инструкция для клиента
                </label>
                <textarea name="instruction_client" class="form-control" rows="4" required>{{ old('instruction_client', $task->instruction_client) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <span class="role-badge role-badge--observer">Наблюдатель</span>
                    Инструкция для наблюдателя
                </label>
                <textarea name="instruction_observer" class="form-control" rows="4" required>{{ old('instruction_observer', $task->instruction_observer) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Переотправить на модерацию</button>
                <a href="{{ route('triads.tasks.my') }}" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
