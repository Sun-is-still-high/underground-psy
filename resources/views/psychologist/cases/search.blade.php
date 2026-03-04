@extends('layouts.main')

@section('title', 'Поиск кейсов - Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Поиск кейсов</h1>
    </div>
    <p class="subtitle">Найдите клиентов, которым вы можете помочь</p>

    <div class="stats-grid">
        @foreach ($stats as $stat)
            <a href="?problem_type={{ $stat->id }}" class="stat-card {{ request('problem_type') == $stat->id ? 'stat-active' : '' }}">
                <div class="stat-value">{{ $stat->cases_count }}</div>
                <div class="stat-label">{{ $stat->name }}</div>
            </a>
        @endforeach
    </div>

    <div class="filters-bar">
        <form action="{{ route('psychologist.cases.index') }}" method="GET" class="filter-form">
            <div class="filter-group">
                <label>Тип проблемы:</label>
                <select name="problem_type" class="form-control">
                    <option value="">Все типы</option>
                    @foreach ($problemTypes as $type)
                        <option value="{{ $type->id }}" {{ request('problem_type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Формат оплаты:</label>
                <select name="budget_type" class="form-control">
                    <option value="">Любой</option>
                    <option value="PAID" {{ request('budget_type') === 'PAID' ? 'selected' : '' }}>Платно</option>
                    <option value="REVIEW" {{ request('budget_type') === 'REVIEW' ? 'selected' : '' }}>За отзыв</option>
                    <option value="NEGOTIABLE" {{ request('budget_type') === 'NEGOTIABLE' ? 'selected' : '' }}>Договорная</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Применить</button>
            @if (request('problem_type') || request('budget_type'))
                <a href="{{ route('psychologist.cases.index') }}" class="btn btn-outline btn-sm">Сбросить</a>
            @endif
        </form>
    </div>

    @php $budgetLabels = ['PAID' => 'Платно', 'REVIEW' => 'За отзыв', 'NEGOTIABLE' => 'Договорная']; @endphp

    @if ($cases->isEmpty())
        <div class="empty-state">
            <p>Кейсов по выбранным фильтрам не найдено</p>
        </div>
    @else
        <div class="cases-list">
            @foreach ($cases as $case)
                <div class="case-card">
                    <div class="case-header">
                        <h3>{{ $case->title }}</h3>
                        <span class="badge badge-{{ strtolower(str_replace('_', '-', $case->budget_type)) }}">
                            {{ $budgetLabels[$case->budget_type] ?? $case->budget_type }}
                        </span>
                    </div>
                    <div class="case-meta">
                        <span class="problem-type">{{ $case->problemType->name }}</span>
                        @if (!$case->is_anonymous)
                            <span class="client-name">от {{ $case->client->name }}</span>
                        @else
                            <span class="client-name">Анонимный запрос</span>
                        @endif
                        <span class="date">{{ $case->created_at->format('d.m.Y') }}</span>
                    </div>
                    <p class="case-description">{{ mb_substr($case->description, 0, 200) }}...</p>
                    @if ($case->budget_type === 'PAID' && $case->budget_amount)
                        <div class="case-budget">Бюджет: <strong>{{ number_format($case->budget_amount, 0, '', ' ') }} руб.</strong></div>
                    @endif
                    <div class="case-actions">
                        <a href="{{ route('psychologist.cases.show', $case->id) }}" class="btn btn-primary btn-sm">Подробнее</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
