@extends('layouts.main')

@section('title', 'Задание: {{ $task->title }} — Admin')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>{{ $task->title }}</h1>
            <p class="page-subtitle">
                Автор: {{ $task->author->name }} ·
                {{ $task->created_at->format('d.m.Y') }} ·
                <span class="badge badge-outline">{{ $task->duration_minutes }} мин</span>
            </p>
        </div>
        <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline">← К списку</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start;">

        {{-- Содержимое задания (форма для редактирования) --}}
        <div>
            <form method="POST" action="{{ route('admin.tasks.update', $task) }}" id="task-form">
                @csrf
                @method('PUT')

                <div class="card" style="padding: 1.5rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Название</label>
                        <input type="text" name="title" value="{{ old('title', $task->title) }}"
                               class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Описание кейса</label>
                        <textarea name="description" class="form-control" rows="4" required>{{ old('description', $task->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Длительность (мин)</label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $task->duration_minutes) }}"
                               class="form-control" style="max-width: 120px;" min="10" max="180" required>
                    </div>
                </div>

                <div class="card" style="padding: 1.5rem; margin-bottom: 1rem;">
                    <p class="form-section-title">Инструкции</p>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="role-badge role-badge--therapist">Терапевт</span>
                        </label>
                        <textarea name="instruction_therapist" class="form-control" rows="4" required>{{ old('instruction_therapist', $task->instruction_therapist) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="role-badge role-badge--client">Клиент</span>
                        </label>
                        <textarea name="instruction_client" class="form-control" rows="4" required>{{ old('instruction_client', $task->instruction_client) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <span class="role-badge role-badge--observer">Наблюдатель</span>
                        </label>
                        <textarea name="instruction_observer" class="form-control" rows="4" required>{{ old('instruction_observer', $task->instruction_observer) }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-outline btn-sm">Сохранить правки</button>
            </form>
        </div>

        {{-- Панель действий --}}
        <div>
            <div class="card" style="padding: 1.25rem;">
                <p class="form-section-title">Действие</p>

                @if($task->status->value === 'pending')
                    {{-- Одобрить --}}
                    <form method="POST" action="{{ route('admin.tasks.approve', $task) }}" style="margin-bottom: 1rem;">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Одобрить
                        </button>
                        <p class="form-hint">Если нужны правки — сначала сохраните их, затем одобрите</p>
                    </form>

                    {{-- Отклонить --}}
                    <form method="POST" action="{{ route('admin.tasks.reject', $task) }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Причина отклонения</label>
                            <textarea name="moderation_comment" class="form-control" rows="3"
                                      placeholder="Что нужно исправить..." required>{{ old('moderation_comment') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-danger" style="width: 100%;">Отклонить</button>
                    </form>

                @elseif($task->status->value === 'approved')
                    <div class="alert alert-success">Задание одобрено и опубликовано в банке</div>

                @elseif($task->status->value === 'rejected')
                    <div class="alert alert-error">
                        <strong>Отклонено.</strong>
                        @if($task->moderation_comment)
                            <br>{{ $task->moderation_comment }}
                        @endif
                    </div>
                    {{-- Можно повторно одобрить --}}
                    <form method="POST" action="{{ route('admin.tasks.approve', $task) }}" style="margin-top: 1rem;">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Одобрить всё же</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
