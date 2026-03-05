@extends('layouts.main')

@section('title', 'Вопросы и ответы — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Вопросы и ответы</h1>
        <p class="page-subtitle">Психологи отвечают на вопросы пользователей</p>
        <a href="{{ route('questions.ask') }}" class="btn btn-primary">Задать вопрос</a>
    </div>

    @if ($questions->isEmpty())
        <div class="empty-state">
            <p>Пока нет отвеченных вопросов. <a href="{{ route('questions.ask') }}">Задайте первый!</a></p>
        </div>
    @else
        <div class="qa-list">
            @foreach ($questions as $question)
                <div class="qa-item">
                    <div class="qa-question-block">
                        <div class="qa-question-meta">
                            <span class="qa-author">{{ $question->author_name }}</span>
                            <span class="qa-date">{{ $question->created_at->format('d.m.Y') }}</span>
                        </div>
                        <p class="qa-question-text">{!! nl2br(e($question->question)) !!}</p>
                    </div>

                    @foreach ($question->answers as $answer)
                        <div class="qa-answer-block">
                            <div class="qa-answer-meta">
                                @if ($answer->psychologist?->psychologistProfile?->photo_url)
                                    <img src="{{ $answer->psychologist->psychologistProfile->photo_url }}"
                                         alt="{{ $answer->psychologist->name }}" class="qa-avatar">
                                @endif
                                <strong>{{ $answer->psychologist?->name ?? 'Психолог' }}</strong>
                                <span class="qa-date">{{ $answer->created_at->format('d.m.Y') }}</span>
                            </div>
                            <p class="qa-answer-text">{!! nl2br(e($answer->answer)) !!}</p>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
