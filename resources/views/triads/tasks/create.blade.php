@extends('layouts.main')

@section('title', 'Предложить задание — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Предложить задание</h1>
            <p class="page-subtitle">Задание пройдёт модерацию перед публикацией в банке</p>
        </div>
        <a href="{{ route('triads.tasks.index') }}" class="btn btn-outline">← Банк заданий</a>
    </div>

    <div class="form-card card">
        <form method="POST" action="{{ route('triads.tasks.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Название задания</label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="form-control" placeholder="Например: Разочарование в себе" required>
            </div>

            <div class="form-group">
                <label class="form-label">Описание кейса</label>
                <textarea name="description" class="form-control" rows="4"
                          placeholder="Общее описание ситуации клиента..." required>{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Длительность сессии (минут)</label>
                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 50) }}"
                       class="form-control" style="max-width: 120px;" min="10" max="180" required>
                <p class="form-hint">От 10 до 180 минут</p>
            </div>

            <hr class="form-divider">
            <p class="form-section-title">Инструкции по ролям</p>

            <div class="form-group">
                <label class="form-label">
                    <span class="role-badge role-badge--therapist">Терапевт</span>
                    Инструкция для терапевта
                </label>
                <textarea name="instruction_therapist" class="form-control" rows="4"
                          placeholder="Что знает и делает терапевт в этой сессии..." required>{{ old('instruction_therapist') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <span class="role-badge role-badge--client">Клиент</span>
                    Инструкция для клиента
                </label>
                <textarea name="instruction_client" class="form-control" rows="4"
                          placeholder="Что знает и делает клиент, какую роль разыгрывает..." required>{{ old('instruction_client') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <span class="role-badge role-badge--observer">Наблюдатель</span>
                    Инструкция для наблюдателя
                </label>
                <textarea name="instruction_observer" class="form-control" rows="4"
                          placeholder="На что обращает внимание наблюдатель..." required>{{ old('instruction_observer') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Отправить на модерацию</button>
                <a href="{{ route('triads.tasks.index') }}" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
