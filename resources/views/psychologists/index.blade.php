@extends('layouts.main')

@section('title', 'Психологи - Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Наши психологи</h1>
        <p class="page-subtitle">Начинающие специалисты, готовые помочь</p>
    </div>

    <div class="filters-bar">
        <form action="{{ route('psychologists.index') }}" method="GET" class="filter-form">
            <div class="filter-group">
                <select name="specialization" class="form-control">
                    <option value="">Все специализации</option>
                    @foreach ($problemTypes as $type)
                        <option value="{{ $type->id }}" {{ request('specialization') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <input type="text" name="search" class="form-control" placeholder="Поиск по имени..."
                       value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-primary">Найти</button>
            @if (request('specialization') || request('search'))
                <a href="{{ route('psychologists.index') }}" class="btn btn-outline">Сбросить</a>
            @endif
        </form>
    </div>

    @if ($psychologists->isEmpty())
        <div class="empty-state">
            <p>Психологов по выбранным критериям не найдено.</p>
        </div>
    @else
        <div class="psychologists-grid">
            @foreach ($psychologists as $psy)
                <div class="psychologist-card">
                    <div class="psy-card-header">
                        <div class="psy-avatar">{{ mb_substr($psy->user->name, 0, 1) }}</div>
                        <div>
                            <h3 class="psy-name">{{ $psy->user->name }}</h3>
                            <span class="psy-since">На платформе с {{ $psy->user->created_at->format('d.m.Y') }}</span>
                        </div>
                    </div>

                    @if ($psy->problemTypes->isNotEmpty())
                        <div class="psy-specializations">
                            @foreach ($psy->problemTypes as $spec)
                                <span class="badge badge-primary">{{ $spec->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($psy->bio)
                        <p class="psy-bio">{{ mb_substr($psy->bio, 0, 150) }}{{ mb_strlen($psy->bio) > 150 ? '...' : '' }}</p>
                    @endif

                    @if ($psy->hourly_rate_min || $psy->hourly_rate_max)
                        <div class="psy-rate">
                            @if ($psy->hourly_rate_min && $psy->hourly_rate_max)
                                {{ number_format($psy->hourly_rate_min, 0, '', ' ') }} – {{ number_format($psy->hourly_rate_max, 0, '', ' ') }} ₽/час
                            @elseif ($psy->hourly_rate_min)
                                от {{ number_format($psy->hourly_rate_min, 0, '', ' ') }} ₽/час
                            @else
                                до {{ number_format($psy->hourly_rate_max, 0, '', ' ') }} ₽/час
                            @endif
                        </div>
                    @endif

                    <div class="psy-card-actions">
                        <a href="{{ route('psychologists.show', $psy->id) }}" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
