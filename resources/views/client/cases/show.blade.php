@extends('layouts.main')

@section('title', $case->title . ' - Underground Psy')

@section('content')
<div class="container">
    @php
    $statusLabels = ['OPEN' => 'Открыт', 'IN_PROGRESS' => 'В работе', 'CLOSED' => 'Закрыт', 'CANCELLED' => 'Отменён'];
    $budgetLabels = ['PAID' => 'Платно', 'REVIEW' => 'Оплата отзывом', 'NEGOTIABLE' => 'Договорная'];
    $responseStatuses = ['PENDING' => 'Ожидает', 'ACCEPTED' => 'Принят', 'REJECTED' => 'Отклонён'];
    @endphp

    <div class="page-header">
        <div>
            <a href="{{ route('client.cases.index') }}" class="nav-link">&larr; Назад к списку</a>
            <h1>{{ $case->title }}</h1>
        </div>
        <span class="badge badge-{{ strtolower($case->status) }}">
            {{ $statusLabels[$case->status] ?? $case->status }}
        </span>
    </div>

    <div class="case-details">
        <div class="case-info-card">
            <div class="info-row"><span class="label">Тип проблемы:</span><span class="value">{{ $case->problemType->name }}</span></div>
            <div class="info-row"><span class="label">Дата создания:</span><span class="value">{{ $case->created_at->format('d.m.Y H:i') }}</span></div>
            <div class="info-row">
                <span class="label">Формат оплаты:</span>
                <span class="value">
                    {{ $budgetLabels[$case->budget_type] ?? $case->budget_type }}
                    @if ($case->budget_type === 'PAID' && $case->budget_amount)
                        ({{ number_format($case->budget_amount, 0, '', ' ') }} руб.)
                    @endif
                </span>
            </div>
            <div class="info-row"><span class="label">Анонимность:</span><span class="value">{{ $case->is_anonymous ? 'Да' : 'Нет' }}</span></div>
        </div>

        <div class="description-box">
            <h3>Описание</h3>
            <p>{!! nl2br(e($case->description)) !!}</p>
        </div>

        @if ($case->status === 'OPEN')
            <form action="{{ route('client.cases.close', $case->id) }}" method="POST" style="margin-bottom: 2rem;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm"
                        onclick="return confirm('Вы уверены, что хотите закрыть этот запрос?')">
                    Закрыть запрос
                </button>
            </form>
        @endif
    </div>

    <div class="section">
        <h2>Отклики психологов ({{ $case->responses->count() }})</h2>

        @if ($case->responses->isEmpty())
            <div class="empty-message">
                <p>Пока никто не откликнулся на ваш запрос</p>
            </div>
        @else
            <div class="responses-list">
                @foreach ($case->responses as $response)
                    <div class="response-card {{ $response->status === 'ACCEPTED' ? 'response-accepted' : '' }}">
                        <div class="response-header">
                            <div class="psychologist-info">
                                <strong>{{ $response->psychologist->name }}</strong>
                                <span class="email">{{ $response->psychologist->email }}</span>
                            </div>
                            <span class="badge badge-{{ strtolower($response->status) }}">
                                {{ $responseStatuses[$response->status] ?? $response->status }}
                            </span>
                        </div>
                        <div class="response-message">
                            <p>{!! nl2br(e($response->message)) !!}</p>
                        </div>
                        @if ($response->proposed_price)
                            <div class="proposed-price">
                                Предложенная цена: <strong>{{ number_format($response->proposed_price, 0, '', ' ') }} руб.</strong>
                            </div>
                        @endif
                        <div class="response-footer">
                            <span class="date">{{ $response->created_at->format('d.m.Y H:i') }}</span>
                            @if ($case->status === 'OPEN' && $response->status === 'PENDING')
                                <form action="{{ route('client.cases.accept-response', [$case->id, $response->id]) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm"
                                            onclick="return confirm('Принять этот отклик? Остальные будут отклонены.')">
                                        Принять отклик
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
