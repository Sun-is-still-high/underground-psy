@extends('layouts.main')

@section('title', 'Редактирование профиля - Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Редактирование профиля</h1>
        <p class="page-subtitle">Заполните информацию о себе для клиентов</p>
    </div>

    <form action="{{ route('psychologist.profile.update') }}" method="POST" class="profile-form">
        @csrf

        <div class="form-group">
            <label for="bio">О себе <span class="required">*</span></label>
            <textarea name="bio" id="bio" class="form-control" rows="5"
                      placeholder="Расскажите о себе, своём подходе к работе...">{{ old('bio', $profile->bio) }}</textarea>
            <small class="form-hint">Обязательно для публикации профиля</small>
        </div>

        <div class="form-group">
            <label for="methods_description">Методы и подходы</label>
            <textarea name="methods_description" id="methods_description" class="form-control" rows="4"
                      placeholder="КПТ, психоанализ, гештальт...">{{ old('methods_description', $profile->methods_description) }}</textarea>
        </div>

        <div class="form-group">
            <label>Специализации</label>
            <div class="checkbox-group">
                @foreach ($problemTypes as $type)
                    <label class="checkbox-label">
                        <input type="checkbox" name="specializations[]" value="{{ $type->id }}"
                               {{ $profile->problemTypes->contains($type->id) ? 'checked' : '' }}>
                        {{ $type->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label for="education">Образование</label>
            <textarea name="education" id="education" class="form-control" rows="3"
                      placeholder="Укажите образование, курсы, сертификаты...">{{ old('education', $profile->education) }}</textarea>
        </div>

        <div class="form-group">
            <label for="experience_description">Опыт работы</label>
            <textarea name="experience_description" id="experience_description" class="form-control" rows="3">{{ old('experience_description', $profile->experience_description) }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="hourly_rate_min">Минимальная ставка (₽/час)</label>
                <input type="number" name="hourly_rate_min" id="hourly_rate_min" class="form-control" step="100" min="0"
                       value="{{ old('hourly_rate_min', $profile->hourly_rate_min) }}">
            </div>
            <div class="form-group">
                <label for="hourly_rate_max">Максимальная ставка (₽/час)</label>
                <input type="number" name="hourly_rate_max" id="hourly_rate_max" class="form-control" step="100" min="0"
                       value="{{ old('hourly_rate_max', $profile->hourly_rate_max) }}">
            </div>
        </div>

        <div class="form-group" style="margin-top: 1.5rem;">
            <label class="checkbox-label">
                <input type="checkbox" name="is_published" value="1" {{ $profile->is_published ? 'checked' : '' }}>
                Опубликовать профиль (сделать видимым для клиентов)
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Сохранить профиль</button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline">Отмена</a>
            @if ($profile->is_published)
                <a href="{{ route('psychologists.show', $profile->id) }}" class="btn btn-outline">Посмотреть профиль</a>
            @endif
        </div>
    </form>
</div>
@endsection
