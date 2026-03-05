@extends('layouts.main')

@section('title', 'Вопросы пользователей — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Вопросы пользователей</h1>
        <p class="page-subtitle">Ответьте на вопросы — ваш ответ будет опубликован публично</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('errors'))
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach (session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
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

    @if ($questions->isEmpty())
        <div class="empty-state">
            <p>Новых вопросов пока нет. Загляните позже.</p>
        </div>
    @else
        <div class="qa-admin-list">
            @foreach ($questions as $question)
                <div class="qa-admin-item">
                    <div class="qa-question-block">
                        <div class="qa-question-meta">
                            <span class="qa-author">{{ $question->author_name }}</span>
                            <span class="qa-date">{{ $question->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <p class="qa-question-text">{!! nl2br(e($question->question)) !!}</p>
                    </div>

                    <form action="{{ route('psychologist.questions.answer', $question) }}" method="POST" class="qa-answer-form">
                        @csrf
                        <div class="form-group">
                            <label>Ваш ответ</label>
                            <textarea name="answer" class="form-control" rows="4"
                                      placeholder="Напишите развёрнутый ответ..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Опубликовать ответ</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
