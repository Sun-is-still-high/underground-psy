@extends('layouts.main')

@section('title', 'Мои запросы - Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Мои запросы на помощь</h1>
        <a href="{{ route('client.cases.create') }}" class="btn btn-primary">Создать запрос</a>
    </div>

    @if ($cases->isEmpty())
        <div class="empty-state">
            <p>У вас пока нет запросов на помощь</p>
            <p>Создайте запрос, чтобы психологи могли откликнуться и предложить свои услуги</p>
            <a href="{{ route('client.cases.create') }}" class="btn btn-primary">Создать первый запрос</a>
        </div>
    @else
        @php
        $statusLabels = ['OPEN' => 'Открыт', 'IN_PROGRESS' => 'В работе', 'CLOSED' => 'Закрыт', 'CANCELLED' => 'Отменён'];
        @endphp
        <div class="cases-list">
            @foreach ($cases as $case)
                <div class="case-card">
                    <div class="case-header">
                        <h3>{{ $case->title }}</h3>
                        <span class="badge badge-{{ strtolower($case->status) }}">
                            {{ $statusLabels[$case->status] ?? $case->status }}
                        </span>
                    </div>
                    <div class="case-meta">
                        <span class="problem-type">{{ $case->problemType->name }}</span>
                        <span class="date">{{ $case->created_at->format('d.m.Y') }}</span>
                        @if ($case->responses_count > 0)
                            <span class="responses-count">{{ $case->responses_count }} откликов</span>
                        @endif
                    </div>
                    <p class="case-description">{{ mb_substr($case->description, 0, 150) }}...</p>
                    <div class="case-actions">
                        <a href="{{ route('client.cases.show', $case->id) }}" class="btn btn-outline btn-sm">Подробнее</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
