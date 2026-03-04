@extends('layouts.main')

@section('title', $case->title . ' - Underground Psy')

@section('content')
<div class="container">
    @php
    $statusLabels = ['OPEN' => 'Открыт', 'IN_PROGRESS' => 'В работе', 'CLOSED' => 'Закрыт', 'CANCELLED' => 'Отменён'];
    $budgetLabels = ['PAID' => 'Платно', 'REVIEW' => 'Оплата отзывом', 'NEGOTIABLE' => 'Договорная'];
    @endphp

    <div class="page-header">
        <div>
            <a href="{{ route('psychologist.cases.index') }}" class="nav-link">&larr; Назад к поиску</a>
            <h1>{{ $case->title }}</h1>
        </div>
        <span class="badge badge-{{ strtolower(str_replace('_', '-', $case->budget_type)) }}">
            {{ $budgetLabels[$case->budget_type] ?? $case->budget_type }}
        </span>
    </div>

    <div class="case-details">
        <div class="case-info-card">
            <div class="info-row"><span class="label">Клиент:</span><span class="value">{{ $case->is_anonymous ? 'Анонимно' : $case->client->name }}</span></div>
            <div class="info-row"><span class="label">Тип проблемы:</span><span class="value">{{ $case->problemType->name }}</span></div>
            <div class="info-row"><span class="label">Дата публикации:</span><span class="value">{{ $case->created_at->format('d.m.Y H:i') }}</span></div>
            @if ($case->budget_type === 'PAID' && $case->budget_amount)
                <div class="info-row"><span class="label">Бюджет:</span><span class="value">{{ number_format($case->budget_amount, 0, '', ' ') }} руб.</span></div>
            @endif
            <div class="info-row"><span class="label">Статус:</span><span class="value">{{ $statusLabels[$case->status] ?? $case->status }}</span></div>
        </div>

        <div class="description-box">
            <h3>Описание проблемы</h3>
            <p>{!! nl2br(e($case->description)) !!}</p>
        </div>

        @if ($case->status === 'OPEN')
            @if ($hasResponded)
                <div class="alert alert-success">
                    <p><strong>Вы уже откликнулись на этот кейс</strong></p>
                    <p>Ожидайте ответа от клиента</p>
                </div>
            @else
                <div class="section">
                    <h2>Откликнуться на кейс</h2>
                    <div class="form-container">
                        <form action="{{ route('psychologist.cases.respond', $case->id) }}" method="POST" class="auth-form">
                            @csrf
                            <div class="form-group">
                                <label for="message">Ваше сообщение клиенту *</label>
                                <textarea name="message" id="message" class="form-control" rows="5"
                                          placeholder="Представьтесь, расскажите о своём опыте..." required>{{ old('message') }}</textarea>
                            </div>
                            @if ($case->budget_type !== 'REVIEW')
                                <div class="form-group">
                                    <label for="proposed_price">Ваша цена за консультацию (руб.)</label>
                                    <input type="number" name="proposed_price" id="proposed_price" class="form-control"
                                           placeholder="Оставьте пустым для договорной цены" min="0" step="100"
                                           value="{{ old('proposed_price') }}">
                                </div>
                            @endif
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Отправить отклик</button>
                                <a href="{{ route('psychologist.cases.index') }}" class="btn btn-outline">Отмена</a>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @else
            <div class="alert alert-warning">
                <p>Этот кейс уже не принимает отклики (статус: {{ $statusLabels[$case->status] ?? $case->status }})</p>
            </div>
        @endif
    </div>
</div>
@endsection
