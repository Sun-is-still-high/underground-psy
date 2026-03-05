@extends('layouts.main')

@section('title', 'Создание группы интервизий')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Создание группы интервизий</h1>
        <a href="{{ route('admin.intervision.groups') }}" class="btn btn-outline">Назад к списку</a>
    </div>

    <div class="form-container">
        <form action="{{ route('admin.intervision.groups.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Название группы *</label>
                <input type="text" id="name" name="name" required
                       value="{{ old('name') }}"
                       placeholder="Например: Группа интервизий #1"
                       class="{{ $errors->has('name') ? 'is-invalid' : '' }}">
                @error('name') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="4"
                          placeholder="Опишите цели и специфику группы">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label for="max_participants">Максимум участников *</label>
                <input type="number" id="max_participants" name="max_participants"
                       min="2" max="50" value="{{ old('max_participants', 10) }}">
                <small>От 2 до 50 человек</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Создать группу</button>
                <a href="{{ route('admin.intervision.groups') }}" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
