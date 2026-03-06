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

            <div class="filter-group">
                <select name="gender" class="form-control">
                    <option value="">Любой пол</option>
                    <option value="male"   {{ request('gender') === 'male'   ? 'selected' : '' }}>Мужчина</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Женщина</option>
                </select>
            </div>

            <div class="filter-group" style="display:flex;gap:6px;align-items:center;">
                <input type="number" name="price_min" class="form-control" placeholder="Цена от"
                       value="{{ request('price_min') }}" min="0" style="width:110px;">
                <span style="color:#6b7280;">–</span>
                <input type="number" name="price_max" class="form-control" placeholder="до"
                       value="{{ request('price_max') }}" min="0" style="width:110px;">
                <small style="color:#6b7280;">₽/час</small>
            </div>

            @if ($methods->isNotEmpty())
            <div class="filter-group">
                <select name="method" class="form-control">
                    <option value="">Все методы</option>
                    @foreach ($methods as $m)
                        <option value="{{ $m->id }}" {{ request('method') == $m->id ? 'selected' : '' }}>
                            {{ $m->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="filter-group">
                <select name="work_format" class="form-control">
                    <option value="">Любой формат</option>
                    <option value="online"  {{ request('work_format') === 'online'  ? 'selected' : '' }}>Онлайн</option>
                    <option value="offline" {{ request('work_format') === 'offline' ? 'selected' : '' }}>Офлайн</option>
                </select>
            </div>

            <div class="filter-group">
                <input type="text" name="city" class="form-control" placeholder="Город"
                       value="{{ request('city') }}">
            </div>

            <div class="filter-group">
                <select name="language" class="form-control">
                    <option value="">Любой язык</option>
                    @foreach (['ru' => 'Русский', 'en' => 'Английский', 'de' => 'Немецкий', 'fr' => 'Французский', 'es' => 'Испанский'] as $code => $label)
                        <option value="{{ $code }}" {{ request('language') === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <select name="sort" class="form-control">
                    <option value="activity" {{ request('sort', 'activity') === 'activity' ? 'selected' : '' }}>По активности</option>
                    <option value="price"    {{ request('sort') === 'price'    ? 'selected' : '' }}>По цене (возр.)</option>
                    <option value="since"    {{ request('sort') === 'since'    ? 'selected' : '' }}>По стажу</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Найти</button>
            @if (request('specialization') || request('search') || request('gender') || request('price_min') || request('price_max') || request('method') || request('work_format') || request('city') || request('language'))
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
                @php $triads = $psy->user->triadCounts(); @endphp
                <div class="psychologist-card">
                    <div class="psy-card-header">
                        <div class="psy-avatar">{{ mb_substr($psy->user->name, 0, 1) }}</div>
                        <div>
                            <h3 class="psy-name">{{ $psy->user->name }}</h3>
                            <span class="psy-since">
                                @if ($psy->user->gender === 'female') Психолог @else Психолог @endif
                                с {{ $psy->user->created_at->format('Y') }} г.
                            </span>
                        </div>
                    </div>

                    @if ($psy->problemTypes->isNotEmpty())
                        <div class="psy-specializations">
                            @foreach ($psy->problemTypes as $spec)
                                <span class="badge badge-primary">{{ $spec->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($psy->methods->isNotEmpty())
                        <div class="psy-specializations" style="margin-top:4px;">
                            @foreach ($psy->methods as $m)
                                <span class="badge" style="background:#e0f2fe;color:#075985;">{{ $m->name }}</span>
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

                    {{-- Формат и город --}}
                    @if ($psy->work_format)
                        <div class="psy-format" style="font-size:0.82rem;color:#6b7280;margin-bottom:4px;">
                            @if ($psy->work_format === 'online') Онлайн
                            @elseif ($psy->work_format === 'offline') Офлайн{{ $psy->city ? ' · ' . $psy->city : '' }}
                            @else Онлайн и офлайн{{ $psy->city ? ' · ' . $psy->city : '' }}
                            @endif
                        </div>
                    @endif

                    {{-- Метрики --}}
                    @php $intervisions = $psy->user->intervisionCount(); @endphp
                    <div class="psy-metrics">
                        @if ($triads['total'] > 0)
                            <span class="psy-metric" title="Тройки">🔺 {{ $triads['total'] }} тр.</span>
                        @endif
                        @if ($intervisions > 0)
                            <span class="psy-metric" title="Интервизии">👥 {{ $intervisions }} инт.</span>
                        @endif
                        <span class="psy-metric" title="На платформе с">📅 с {{ $psy->user->created_at->format('d.m.Y') }}</span>
                    </div>

                    <div class="psy-card-actions">
                        <a href="{{ route('psychologists.show', $psy->id) }}" class="btn btn-primary">Подробнее</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
