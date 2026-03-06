@extends('layouts.main')

@section('title', 'Верификация дипломов — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Верификация дипломов</h1>
        <p class="page-subtitle">Психологи, загрузившие документы об образовании</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($profiles->isEmpty())
        <div class="empty-state">
            <p>Нет загруженных дипломов для проверки.</p>
        </div>
    @else
        <div class="verification-list">
            @foreach ($profiles as $profile)
                <div class="verification-item {{ $profile->diploma_verified ? 'verified' : 'pending' }}">
                    <div class="verification-info">
                        <strong>{{ $profile->user->name }}</strong>
                        <span class="text-muted">{{ $profile->user->email }}</span>
                        <span class="text-muted">Статус: {{ $profile->user->status === 'active' ? 'Активен' : 'Ожидает верификации' }}</span>

                        @if ($profile->diploma_number)
                            <div><small>Диплом №{{ $profile->diploma_number }}, {{ $profile->diploma_year }} г., {{ $profile->diploma_institution }}</small></div>
                        @endif

                        @if ($profile->diploma_rejection_comment)
                            <div class="alert alert-warning mt-1" style="font-size: 0.9em;">
                                <strong>Предыдущий комментарий отклонения:</strong> {{ $profile->diploma_rejection_comment }}
                            </div>
                        @endif
                    </div>

                    <div class="verification-diploma">
                        @if ($profile->diploma_scan_url)
                            @php $ext = strtolower(pathinfo($profile->diploma_scan_url, PATHINFO_EXTENSION)); @endphp
                            @if (in_array($ext, ['jpg', 'jpeg', 'png']))
                                <a href="{{ route('admin.verification.diploma', $profile) }}" target="_blank">
                                    <img src="{{ route('admin.verification.diploma', $profile) }}" alt="Диплом" class="diploma-preview" style="max-width:200px;">
                                </a>
                            @else
                                <a href="{{ route('admin.verification.diploma', $profile) }}" target="_blank" class="btn btn-outline">
                                    Открыть PDF
                                </a>
                            @endif
                        @else
                            <span class="text-muted">Скан не загружен</span>
                        @endif
                    </div>

                    <div class="verification-actions">
                        @if ($profile->diploma_verified)
                            <span class="badge badge-success">Верифицирован</span>
                        @else
                            <span class="badge badge-warning">Ожидает проверки</span>

                            <form action="{{ route('admin.verification.approve', $profile) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Подтвердить</button>
                            </form>

                            <form action="{{ route('admin.verification.reject', $profile) }}" method="POST"
                                  class="reject-form" style="margin-top: 0.5rem;">
                                @csrf
                                <textarea name="comment" class="form-control" rows="2" placeholder="Причина отклонения (обязательно)" required
                                          style="margin-bottom: 0.4rem; font-size: 0.9em;"></textarea>
                                <button type="submit" class="btn btn-outline btn-sm">Отклонить</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
