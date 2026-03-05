@extends('layouts.main')

@section('title', 'Мои задания — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Мои задания</h1>
            <p class="page-subtitle">Статусы предложенных вами заданий</p>
        </div>
        <a href="{{ route('triads.tasks.create') }}" class="btn btn-primary">Предложить задание</a>
    </div>

    @if($tasks->isEmpty())
        <div class="empty-state">
            <p>Вы ещё не предлагали заданий.</p>
            <a href="{{ route('triads.tasks.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Предложить первое задание</a>
        </div>
    @else
        <div class="tasks-list">
            @foreach($tasks as $task)
                @php
                    $statusClass = match($task->status->value) {
                        'approved' => 'badge-success',
                        'pending'  => 'badge-warning',
                        'rejected' => 'badge-error',
                        default    => 'badge-secondary',
                    };
                @endphp
                <div class="task-list-item card">
                    <div class="task-list-header">
                        <h3 class="task-title">{{ $task->title }}</h3>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <span class="badge {{ $statusClass }}">{{ $task->status->label() }}</span>
                            <span class="badge badge-outline">{{ $task->duration_minutes }} мин</span>
                        </div>
                    </div>

                    <p class="task-description">{{ Str::limit($task->description, 120) }}</p>

                    @if($task->status->value === 'rejected' && $task->moderation_comment)
                        <div class="alert alert-error" style="margin-top: 0.75rem;">
                            <strong>Причина отклонения:</strong> {{ $task->moderation_comment }}
                        </div>
                    @endif

                    <div class="task-footer">
                        <span class="text-light">{{ $task->created_at->format('d.m.Y') }}</span>
                        @if($task->status->value === 'rejected')
                            <a href="{{ route('triads.tasks.edit', $task) }}" class="btn btn-outline btn-sm">
                                Исправить и переотправить
                            </a>
                        @endif
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
