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
                        <a href="{{ route('psychologists.show', $profile->id) }}" target="_blank" class="btn btn-outline btn-sm">Профиль</a>
                    </div>

                    <div class="verification-diploma">
                        @php $ext = strtolower(pathinfo($profile->diploma_scan_url, PATHINFO_EXTENSION)); @endphp
                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                            <a href="{{ $profile->diploma_scan_url }}" target="_blank">
                                <img src="{{ $profile->diploma_scan_url }}" alt="Диплом" class="diploma-preview">
                            </a>
                        @else
                            <a href="{{ $profile->diploma_scan_url }}" target="_blank" class="btn btn-outline">
                                Открыть PDF
                            </a>
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
                            <form action="{{ route('admin.verification.reject', $profile) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-sm"
                                        onclick="return confirm('Отклонить и удалить скан?')">Отклонить</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
