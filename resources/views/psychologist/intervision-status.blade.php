@extends('layouts.main')

@section('title', 'Мои интервизии — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Мои интервизии</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-outline">Назад в кабинет</a>
    </div>

    @if (!$status['in_group'])
        <div class="warning-box">
            <strong>Вы не состоите ни в одной группе интервизий.</strong>
            Обратитесь к администратору для добавления в группу.
        </div>
    @else
        <div class="status-card {{ $status['can_consult'] ? 'status-ok' : 'status-warning' }}">
            @if ($status['can_consult'])
                <div class="status-icon status-icon-ok">&#10003;</div>
                <h2>Вы можете консультировать</h2>
                <p>Требования по интервизиям на этот месяц выполнены.</p>
            @else
                <div class="status-icon status-icon-warning">!</div>
                <h2>Требуется посещение интервизий</h2>
                <p>Для права консультировать посетите ещё
                    <strong>{{ $status['required_sessions'] - $status['monthly_sessions'] }}</strong>
                    {{ match(true) {
                        ($status['required_sessions'] - $status['monthly_sessions']) === 1 => 'сессию',
                        ($status['required_sessions'] - $status['monthly_sessions']) < 5 => 'сессии',
                        default => 'сессий'
                    } }}
                    в этом месяце.
                </p>
            @endif

            <div class="status-details">
                <div class="progress-info">
                    <span>Посещено в этом месяце:</span>
                    <strong>{{ $status['monthly_sessions'] }} / {{ $status['required_sessions'] }}</strong>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill"
                         style="width: {{ min(100, ($status['monthly_sessions'] / max(1, $status['required_sessions'])) * 100) }}%"></div>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Моя группа интервизий</h2>
            <div class="group-card">
                <h3>{{ $status['group']->name }}</h3>
                @if ($status['group']->description)
                    <p>{{ $status['group']->description }}</p>
                @endif
            </div>
        </div>
    @endif
</div>

<style>
.status-card { padding: 2rem; border-radius: 8px; margin-bottom: 2rem; text-align: center; }
.status-ok { background: #d4edda; border: 1px solid #c3e6cb; }
.status-warning { background: #fff3cd; border: 1px solid #ffeeba; }
.status-icon { font-size: 3rem; margin-bottom: 1rem; }
.status-icon-ok { color: #28a745; }
.status-icon-warning { color: #856404; }
.status-details { margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(0,0,0,0.1); }
.progress-info { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.progress-bar { height: 10px; background: rgba(0,0,0,0.1); border-radius: 5px; overflow: hidden; }
.progress-fill { height: 100%; background: #28a745; transition: width 0.3s; }
.group-card { padding: 1.5rem; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef; }
.group-card h3 { margin: 0 0 0.5rem 0; }
</style>
@endsection
