@extends('layouts.main')

@section('title', 'Создать мероприятие — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Создать мероприятие</h1>
        <p class="page-subtitle">Групповая терапия, семинар, тренинг или группа поддержки</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('psychologist.events.store') }}" method="POST" class="profile-form">
        @csrf

        <div class="form-group">
            <label for="title">Название <span class="required">*</span></label>
            <input type="text" name="title" id="title" class="form-control"
                   placeholder="Например: Группа поддержки при тревоге"
                   value="{{ old('title') }}">
        </div>

        <div class="form-group">
            <label for="event_type">Тип мероприятия <span class="required">*</span></label>
            <select name="event_type" id="event_type" class="form-control">
                <option value="">— Выберите тип —</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}" {{ old('event_type') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="description">Описание</label>
            <textarea name="description" id="description" class="form-control" rows="5"
                      placeholder="Расскажите, что будет на мероприятии, кому подойдёт...">{{ old('description') }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="format">Формат <span class="required">*</span></label>
                <select name="format" id="format" class="form-control" onchange="toggleEventCity(this.value)">
                    @foreach ($formats as $value => $label)
                        <option value="{{ $value }}" {{ old('format', 'ONLINE') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" id="event-city-field"
                 style="{{ old('format', 'ONLINE') === 'OFFLINE' ? '' : 'display:none' }}">
                <label for="city">Город</label>
                <input type="text" name="city" id="city" class="form-control"
                       placeholder="Например: Минск"
                       value="{{ old('city') }}">
            </div>
        </div>

        <div class="form-group" id="meeting-link-field"
             style="{{ old('format', 'ONLINE') === 'ONLINE' ? '' : 'display:none' }}">
            <label for="meeting_link">Ссылка на встречу (Zoom, Meet и т.д.)</label>
            <input type="url" name="meeting_link" id="meeting_link" class="form-control"
                   placeholder="https://..."
                   value="{{ old('meeting_link') }}">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="scheduled_at">Дата и время <span class="required">*</span></label>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control"
                       value="{{ old('scheduled_at') }}">
            </div>
            <div class="form-group">
                <label for="duration_minutes">Продолжительность (мин.)</label>
                <input type="number" name="duration_minutes" id="duration_minutes" class="form-control"
                       min="30" step="30" value="{{ old('duration_minutes', 60) }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="price">Стоимость (₽)</label>
                <input type="number" name="price" id="price" class="form-control"
                       min="0" step="100" placeholder="0 = бесплатно"
                       value="{{ old('price') }}">
            </div>
            <div class="form-group">
                <label for="max_participants">Макс. участников</label>
                <input type="number" name="max_participants" id="max_participants" class="form-control"
                       min="2" placeholder="Пусто = без ограничений"
                       value="{{ old('max_participants') }}">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Опубликовать мероприятие</button>
            <a href="{{ route('events.index') }}" class="btn btn-outline">Отмена</a>
        </div>
    </form>
</div>

<script>
function toggleEventCity(format) {
    document.getElementById('event-city-field').style.display  = format === 'OFFLINE' ? '' : 'none';
    document.getElementById('meeting-link-field').style.display = format === 'ONLINE'  ? '' : 'none';
}
</script>
@endsection
