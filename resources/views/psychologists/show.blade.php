@extends('layouts.main')

@section('title', $profile->user->name . ' — Психолог — Underground Psy')

@section('content')
<div class="container">
    <div class="profile-page">
        <div class="profile-header">
            <div class="profile-avatar-lg">{{ mb_substr($profile->user->name, 0, 1) }}</div>
            <h1>{{ $profile->user->name }}</h1>

            @if ($profile->problemTypes->isNotEmpty())
                <div class="profile-specializations">
                    @foreach ($profile->problemTypes as $spec)
                        <span class="badge badge-primary">{{ $spec->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($profile->methods->isNotEmpty())
            <div class="profile-specializations" style="margin-top:8px;">
                @foreach ($profile->methods as $m)
                    <span class="badge" style="background:#e0f2fe;color:#075985;">{{ $m->name }}</span>
                @endforeach
            </div>
        @endif

        @if ($profile->work_format)
            <p style="color:#6b7280;font-size:0.9rem;margin-top:6px;">
                @if ($profile->work_format === 'online') Онлайн
                @elseif ($profile->work_format === 'offline') Офлайн{{ $profile->city ? ', ' . $profile->city : '' }}
                @else Онлайн и офлайн{{ $profile->city ? ', ' . $profile->city : '' }}
                @endif
            </p>
        @endif

        @php
            $triads = $profile->user->triadCounts();
            $intervisions = $profile->user->intervisionCount();
        @endphp
        <div class="psy-metrics" style="margin-top:12px;">
            @if ($triads['total'] > 0)
                <span class="psy-metric">🔺 Тройки: {{ $triads['total'] }}
                    (терапевт: {{ $triads['therapist'] }}, клиент: {{ $triads['client'] }}, наблюдатель: {{ $triads['observer'] }})
                </span>
            @endif
            @if ($intervisions > 0)
                <span class="psy-metric">👥 Интервизии: {{ $intervisions }}</span>
            @endif
            <span class="psy-metric">📅 На платформе с {{ $profile->user->created_at->format('d.m.Y') }}</span>
        </div>

        <div class="warning-box" style="margin-top:16px;">
            <strong>Обратите внимание:</strong> это начинающий специалист.
            Платформа Underground Psy создана для развития молодых психологов.
        </div>

        <div class="profile-content">
            @if ($profile->bio)
                <div class="profile-section">
                    <h2>О себе</h2>
                    <p>{!! nl2br(e($profile->bio)) !!}</p>
                </div>
            @endif

            @if ($profile->methods_description)
                <div class="profile-section">
                    <h2>Методы и подходы</h2>
                    <p>{!! nl2br(e($profile->methods_description)) !!}</p>
                </div>
            @endif

            @if ($profile->education)
                <div class="profile-section">
                    <h2>Образование</h2>
                    <p>{!! nl2br(e($profile->education)) !!}</p>
                </div>
            @endif

            @if ($profile->experience_description)
                <div class="profile-section">
                    <h2>Опыт работы</h2>
                    <p>{!! nl2br(e($profile->experience_description)) !!}</p>
                </div>
            @endif

            @if ($profile->hourly_rate_min || $profile->hourly_rate_max)
                <div class="profile-section">
                    <h2>Стоимость</h2>
                    <p class="profile-rate">
                        @if ($profile->hourly_rate_min && $profile->hourly_rate_max)
                            {{ number_format($profile->hourly_rate_min, 0, '', ' ') }} – {{ number_format($profile->hourly_rate_max, 0, '', ' ') }} ₽/час
                        @elseif ($profile->hourly_rate_min)
                            от {{ number_format($profile->hourly_rate_min, 0, '', ' ') }} ₽/час
                        @else
                            до {{ number_format($profile->hourly_rate_max, 0, '', ' ') }} ₽/час
                        @endif
                    </p>
                </div>
            @endif
        </div>

        <div class="profile-actions">
            <a href="{{ route('psychologists.index') }}" class="btn btn-outline">← Вернуться к списку</a>
        </div>
    </div>
</div>
@endsection
