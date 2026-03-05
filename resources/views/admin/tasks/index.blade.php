@extends('layouts.main')

@section('title', 'Модерация заданий — Admin')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Модерация заданий</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">← Панель</a>
    </div>

    {{-- Фильтр по статусу --}}
    <div class="filters-form">
        <div class="filters-row">
            @foreach(['pending' => 'На модерации', 'approved' => 'Одобренные', 'rejected' => 'Отклонённые'] as $val => $label)
                <a href="{{ route('admin.tasks.index', ['status' => $val]) }}"
                   class="btn btn-sm {{ $status === $val ? 'btn-primary' : 'btn-outline' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if($tasks->isEmpty())
        <div class="empty-state">
            <p>Нет заданий с таким статусом.</p>
        </div>
    @else
        <div class="tasks-list">
            @foreach($tasks as $task)
                <div class="task-list-item card">
                    <div class="task-list-header">
                        <h3 class="task-title">
                            <a href="{{ route('admin.tasks.show', $task) }}">{{ $task->title }}</a>
                        </h3>
                        <span class="badge badge-outline">{{ $task->duration_minutes }} мин</span>
                    </div>
                    <p class="task-description">{{ Str::limit($task->description, 120) }}</p>
                    <div class="task-footer">
                        <span class="text-light">
                            Автор: {{ $task->author->name }} · {{ $task->created_at->format('d.m.Y') }}
                        </span>
                        <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-outline btn-sm">Рассмотреть</a>
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
