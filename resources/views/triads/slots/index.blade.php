@extends('layouts.main')

@section('title', 'Тройки — Underground Psy')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Психотерапевтические тройки</h1>
            <p class="page-subtitle">Открытые слоты для практики в ролях терапевта, клиента и наблюдателя</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('triads.my-slots') }}" class="btn btn-outline">Мои тройки</a>
            @if(auth()->user()->isPsychologist())
                <a href="{{ route('triads.slots.create') }}" class="btn btn-primary">+ Создать слот</a>
            @endif
        </div>
    </div>

    @livewire('triads.slot-list')
</div>
@endsection
