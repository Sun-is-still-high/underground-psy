@extends('layouts.main')

@section('title', 'Банк заданий — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Банк заданий</h1>
            <p class="page-subtitle">Одобренные задания для психотерапевтических троек</p>
        </div>
        @if(auth()->user()->isPsychologist())
            <div class="page-header-actions">
                <a href="{{ route('triads.tasks.my') }}" class="btn btn-outline">Мои задания</a>
                <a href="{{ route('triads.tasks.create') }}" class="btn btn-primary">Предложить задание</a>
            </div>
        @endif
    </div>

    <form method="GET" action="{{ route('triads.tasks.index') }}" class="filters-form">
        <div class="filters-row">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Поиск по названию..." class="form-control" style="max-width: 320px;">
            <button type="submit" class="btn btn-outline btn-sm">Найти</button>
            @if(request('search'))
                <a href="{{ route('triads.tasks.index') }}" class="btn btn-outline btn-sm">Сбросить</a>
            @endif
        </div>
    </form>

    @if($tasks->isEmpty())
        <div class="empty-state">
            <p>Одобренных заданий пока нет.</p>
        </div>
    @else
        <div class="tasks-grid">
            @foreach($tasks as $task)
                <div class="task-card card">
                    <div class="task-card-header">
                        <h3 class="task-title">{{ $task->title }}</h3>
                        <span class="badge badge-info">{{ $task->duration_minutes }} мин</span>
                    </div>
                    <p class="task-description">{{ Str::limit($task->description, 150) }}</p>
                    <div class="task-footer">
                        <span class="task-author text-light">Автор: {{ $task->author->name }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
@endsection
