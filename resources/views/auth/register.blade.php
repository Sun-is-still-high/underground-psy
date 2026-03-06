@extends('layouts.main')

@section('title', 'Регистрация - Underground Psy')

@section('content')
<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Регистрация</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="auth-form"
                  enctype="multipart/form-data" id="register-form">
                @csrf

                <div class="form-group">
                    <label for="name">Имя</label>
                    <input type="text" id="name" name="name" class="form-control"
                        value="{{ old('name') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="8">
                    <small>Минимум 8 символов</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Подтверждение пароля</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="gender">Пол</label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="not_specified" {{ old('gender', 'not_specified') === 'not_specified' ? 'selected' : '' }}>Не указан</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Мужской</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Женский</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="role">Я регистрируюсь как:</label>
                    <select id="role" name="role" class="form-control">
                        <option value="CLIENT" {{ old('role', 'CLIENT') === 'CLIENT' ? 'selected' : '' }}>Клиент</option>
                        <option value="PSYCHOLOGIST" {{ old('role') === 'PSYCHOLOGIST' ? 'selected' : '' }}>Психолог</option>
                    </select>
                </div>

                {{-- Блок полей диплома — показывается только для психолога --}}
                <div id="diploma-fields" style="display: none;">
                    <hr>
                    <p class="text-muted"><strong>Верификация диплома</strong><br>
                    Для доступа к платформе необходимо подтверждение квалификации. Загрузите скан диплома — он будет проверен модератором в течение 2 недель.</p>

                    <div class="form-group">
                        <label for="diploma_scan">Скан диплома (JPG, PNG или PDF, не более 10 МБ)</label>
                        <input type="file" id="diploma_scan" name="diploma_scan" class="form-control"
                               accept=".jpg,.jpeg,.png,.pdf">
                        @error('diploma_scan') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="diploma_number">Номер диплома</label>
                        <input type="text" id="diploma_number" name="diploma_number" class="form-control"
                               value="{{ old('diploma_number') }}" maxlength="100">
                        @error('diploma_number') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="diploma_year">Год выдачи</label>
                        <input type="number" id="diploma_year" name="diploma_year" class="form-control"
                               value="{{ old('diploma_year') }}" min="1950" max="{{ date('Y') }}">
                        @error('diploma_year') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="diploma_institution">Учебное заведение</label>
                        <input type="text" id="diploma_institution" name="diploma_institution" class="form-control"
                               value="{{ old('diploma_institution') }}" maxlength="255">
                        @error('diploma_institution') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Зарегистрироваться</button>
            </form>

            <div class="auth-footer">
                <p>Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></p>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var roleSelect = document.getElementById('role');
    var diplomaFields = document.getElementById('diploma-fields');
    var diplomaInputs = diplomaFields.querySelectorAll('input');

    function toggleDiploma() {
        var isPsychologist = roleSelect.value === 'PSYCHOLOGIST';
        diplomaFields.style.display = isPsychologist ? 'block' : 'none';
        diplomaInputs.forEach(function (input) {
            input.required = isPsychologist;
        });
    }

    roleSelect.addEventListener('change', toggleDiploma);
    toggleDiploma(); // Восстановить состояние при ошибке валидации
})();
</script>
@endsection
