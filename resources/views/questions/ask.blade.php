@extends('layouts.main')

@section('title', 'Задать вопрос психологу — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Задать вопрос психологу</h1>
        <p class="page-subtitle">Ответ опубликуется публично и поможет другим людям</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('questions.ask.submit') }}" method="POST" class="form-card">
        @csrf

        <div class="form-group">
            <label for="author_name">Ваше имя</label>
            <input type="text" id="author_name" name="author_name"
                   class="form-control" value="{{ old('author_name') }}" required>
        </div>

        <div class="form-group">
            <label for="author_email">Email (ответ придёт на него)</label>
            <input type="email" id="author_email" name="author_email"
                   class="form-control" value="{{ old('author_email') }}" required>
        </div>

        <div class="form-group">
            <label for="question">Ваш вопрос</label>
            <textarea id="question" name="question" class="form-control" rows="6"
                      placeholder="Опишите вашу ситуацию или задайте вопрос..." required>{{ old('question') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Отправить вопрос</button>
        <a href="{{ route('questions.index') }}" class="btn btn-outline">Смотреть ответы</a>
    </form>
</div>
@endsection
