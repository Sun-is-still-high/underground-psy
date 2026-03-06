@extends('layouts.main')

@section('title', 'Методы работы — Админ')

@section('content')
<div class="container">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
        <h1>Методы работы</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm">← Панель</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Форма добавления --}}
    <div class="card" style="padding:1.5rem;margin-bottom:1.5rem;">
        <h3 style="margin-bottom:1rem;">Добавить метод</h3>
        <form action="{{ route('admin.methods.store') }}" method="POST" style="display:flex;gap:8px;">
            @csrf
            <input type="text" name="name" class="form-control" placeholder="Название метода (КПТ, ACT, гештальт...)"
                   maxlength="100" required style="max-width:360px;">
            <button type="submit" class="btn btn-primary">Добавить</button>
        </form>
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    {{-- Список методов --}}
    @if ($methods->isEmpty())
        <div class="empty-state"><p>Методов пока нет.</p></div>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Метод</th>
                    <th>Используют психологов</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($methods as $method)
                <tr>
                    <td>
                        <form action="{{ route('admin.methods.update', $method) }}" method="POST"
                              style="display:flex;gap:6px;" id="method-form-{{ $method->id }}">
                            @csrf @method('PUT')
                            <input type="text" name="name" class="form-control" value="{{ $method->name }}"
                                   maxlength="100" required style="max-width:280px;">
                            <button type="submit" class="btn btn-outline btn-sm">Сохранить</button>
                        </form>
                    </td>
                    <td>{{ $method->profiles_count }}</td>
                    <td>
                        <form action="{{ route('admin.methods.destroy', $method) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Удалить метод «{{ addslashes($method->name) }}»?')">
                                Удалить
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<style>
.admin-table { width:100%; border-collapse:collapse; }
.admin-table th, .admin-table td { padding:10px 12px; border-bottom:1px solid #e5e7eb; font-size:.9rem; vertical-align:middle; }
.admin-table th { background:#f9fafb; font-weight:600; }
.btn-danger { background:#ef4444; color:#fff; border:none; }
.btn-danger:hover { background:#dc2626; }
</style>
@endsection
