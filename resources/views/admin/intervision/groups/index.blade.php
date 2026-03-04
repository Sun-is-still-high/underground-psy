@extends('layouts.main')

@section('title', 'Группы интервизий — Админка')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Группы интервизий</h1>
        <a href="{{ route('admin.intervision.groups.create') }}" class="btn btn-primary">Создать группу</a>
    </div>

    @if ($groups->isEmpty())
        <div class="empty-state">
            <p>Групп пока нет. Создайте первую группу для начала работы.</p>
        </div>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Участников</th>
                    <th>Макс.</th>
                    <th>Создана</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groups as $group)
                <tr>
                    <td><strong>{{ $group->name }}</strong></td>
                    <td>{{ $group->active_participants_count }}</td>
                    <td>{{ $group->max_participants }}</td>
                    <td>{{ $group->created_at->format('d.m.Y') }}</td>
                    <td>
                        <a href="{{ route('admin.intervision.groups.show', $group->id) }}" class="btn btn-sm">Открыть</a>
                        <a href="{{ route('admin.intervision.groups.edit', $group->id) }}" class="btn btn-sm btn-outline">Редактировать</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
