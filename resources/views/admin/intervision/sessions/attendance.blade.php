@extends('layouts.main')

@section('title', 'Отметка посещаемости — ' . $session->topic)

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Отметка посещаемости</h1>
        <a href="{{ route('admin.intervision.sessions.show', $session->id) }}" class="btn btn-outline">Назад к сессии</a>
    </div>

    <div class="session-info">
        <p><strong>Сессия:</strong> {{ $session->topic }}</p>
        <p><strong>Группа:</strong> {{ $session->group->name }}</p>
        <p><strong>Дата:</strong> {{ $session->scheduled_at->format('d.m.Y H:i') }}</p>
    </div>

    @if ($session->attendance->isEmpty())
        <div class="alert alert-warning">
            <p>В группе нет участников для отметки посещаемости.</p>
        </div>
    @else
        <form action="{{ route('admin.intervision.sessions.attendance.save', $session->id) }}" method="POST">
            @csrf

            <div class="attendance-list">
                <p class="help-text">Отметьте участников, которые присутствовали на сессии:</p>

                @foreach ($session->attendance as $record)
                <div class="attendance-item">
                    <label class="checkbox-label">
                        <input type="checkbox"
                               name="attended[]"
                               value="{{ $record->participant_id }}"
                               {{ $record->attended ? 'checked' : '' }}>
                        <span class="participant-name">{{ $record->participant->psychologist->name }}</span>
                        <span class="participant-email">({{ $record->participant->psychologist->email }})</span>
                    </label>
                </div>
                @endforeach
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить посещаемость</button>
                <a href="{{ route('admin.intervision.sessions.show', $session->id) }}" class="btn btn-outline">Отмена</a>
            </div>

            <p class="note">После сохранения сессия будет автоматически отмечена как завершённая.</p>
        </form>
    @endif
</div>
@endsection
