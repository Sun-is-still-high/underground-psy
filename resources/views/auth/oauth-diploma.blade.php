@extends('layouts.main')

@section('title', 'Загрузка диплома - Underground Psy')

@section('content')
<div class="container" style="max-width:560px;margin:3rem auto;">
    <div class="page-header">
        <h1>Добро пожаловать, {{ $oauthData['name'] }}!</h1>
        <p class="page-subtitle">Для работы на платформе психологу необходимо подтвердить квалификацию</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0;padding-left:1.2rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('oauth.diploma.store') }}" method="POST" enctype="multipart/form-data" class="profile-form">
        @csrf

        <div class="form-group">
            <label for="diploma_scan">Скан диплома <span class="required">*</span></label>
            <input type="file" name="diploma_scan" id="diploma_scan" class="form-control"
                   accept=".jpg,.jpeg,.png,.pdf" required>
            <small class="form-hint">JPG, PNG или PDF. Максимум 10 МБ</small>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="diploma_number">Номер диплома <span class="required">*</span></label>
                <input type="text" name="diploma_number" id="diploma_number" class="form-control"
                       value="{{ old('diploma_number') }}" required placeholder="123456">
            </div>
            <div class="form-group">
                <label for="diploma_year">Год выдачи <span class="required">*</span></label>
                <input type="number" name="diploma_year" id="diploma_year" class="form-control"
                       value="{{ old('diploma_year') }}" required min="1950" max="{{ date('Y') }}" placeholder="{{ date('Y') }}">
            </div>
        </div>

        <div class="form-group">
            <label for="diploma_institution">Учебное заведение <span class="required">*</span></label>
            <input type="text" name="diploma_institution" id="diploma_institution" class="form-control"
                   value="{{ old('diploma_institution') }}" required
                   placeholder="МГУ им. Ломоносова">
        </div>

        <div class="form-group">
            <label for="gender">Пол <span class="required">*</span></label>
            <select name="gender" id="gender" class="form-control" required>
                <option value="">Выберите</option>
                <option value="male"          {{ old('gender') === 'male'          ? 'selected' : '' }}>Мужской</option>
                <option value="female"        {{ old('gender') === 'female'        ? 'selected' : '' }}>Женский</option>
                <option value="not_specified" {{ old('gender') === 'not_specified' ? 'selected' : '' }}>Не указывать</option>
            </select>
        </div>

        <div class="alert" style="background:#fefce8;border:1px solid #fde047;color:#713f12;padding:12px 16px;border-radius:8px;font-size:0.9rem;">
            После отправки ваш диплом будет проверен модератором. Срок проверки — до 2 недель.
            Вы получите уведомление о результате.
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Отправить диплом на проверку</button>
        </div>
    </form>
</div>
@endsection
