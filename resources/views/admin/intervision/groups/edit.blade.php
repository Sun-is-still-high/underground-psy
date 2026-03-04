@extends('layouts.main')

@section('title', 'Редактирование группы — ' . $group->name)

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Редактирование группы</h1>
        <a href="{{ route('admin.intervision.groups.show', $group->id) }}" class="btn btn-outline">Назад к группе</a>
    </div>

    <div class="form-container">
        <form action="{{ route('admin.intervision.groups.update', $group->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Название группы *</label>
                <input type="text" id="name" name="name" required
                       value="{{ old('name', $group->name) }}"
                       class="{{ $errors->has('name') ? 'is-invalid' : '' }}">
                @error('name') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="4">{{ old('description', $group->description) }}</textarea>
            </div>

            <div class="form-group">
                <label for="max_participants">Максимум участников *</label>
                <input type="number" id="max_participants" name="max_participants"
                       min="2" max="50" value="{{ old('max_participants', $group->max_participants) }}">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="{{ route('admin.intervision.groups.show', $group->id) }}" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
