@extends('layouts.main')

@section('title', 'Пользователи — Админ')

@section('content')
<div class="container">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
        <h1>Пользователи</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm">← Панель</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Фильтры --}}
    <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;">
        <input type="text" name="q" value="{{ request('q') }}"
               placeholder="Имя или email" class="form-control" style="max-width:260px;">
        <select name="role" class="form-control" style="max-width:180px;">
            <option value="">Все роли</option>
            <option value="CLIENT"       @selected(request('role') === 'CLIENT')>Клиенты</option>
            <option value="PSYCHOLOGIST" @selected(request('role') === 'PSYCHOLOGIST')>Психологи</option>
            <option value="MODERATOR"    @selected(request('role') === 'MODERATOR')>Модераторы</option>
            <option value="ADMIN"        @selected(request('role') === 'ADMIN')>Администраторы</option>
        </select>
        <button type="submit" class="btn btn-primary">Найти</button>
        @if(request('q') || request('role'))
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Сбросить</a>
        @endif
    </form>

    @if ($users->isEmpty())
        <div class="empty-state"><p>Пользователи не найдены.</p></div>
    @else
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Аккаунт</th>
                        <th>Зарегистрирован</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr class="{{ $user->is_blocked ? 'row--blocked' : '' }}">
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>
                            {{ $user->email }}
                            @if (!$user->email_verified_at)
                                <span class="badge badge-warning" title="Email не подтверждён">!</span>
                            @endif
                        </td>
                        <td>
                            @php $roleLabels = ['CLIENT' => 'Клиент', 'PSYCHOLOGIST' => 'Психолог', 'MODERATOR' => 'Модератор', 'ADMIN' => 'Админ']; @endphp
                            {{ $roleLabels[$user->role] ?? $user->role }}
                        </td>
                        <td>
                            @if ($user->is_blocked)
                                <span class="badge badge-error" title="{{ $user->blocked_reason }}">Заблокирован</span>
                            @elseif ($user->status === 'pending_verification')
                                <span class="badge badge-warning">Ожидает верификации</span>
                            @else
                                <span class="badge badge-success">Активен</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d.m.Y') }}</td>
                        <td style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                            {{-- Блокировка --}}
                            @if ($user->is_blocked)
                                <form action="{{ route('admin.users.unblock', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm">Разблокировать</button>
                                </form>
                            @elseif (!$user->isAdmin())
                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="showBlockForm({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                    Заблокировать
                                </button>
                            @endif

                            {{-- Назначение модератора --}}
                            @if ($user->isPsychologist())
                                <form action="{{ route('admin.users.make-moderator', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm"
                                            onclick="return confirm('Назначить {{ addslashes($user->name) }} модератором?')">
                                        + Модератор
                                    </button>
                                </form>
                            @elseif ($user->isModerator())
                                <form action="{{ route('admin.users.remove-moderator', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm"
                                            onclick="return confirm('Снять роль модератора с {{ addslashes($user->name) }}?')">
                                        − Модератор
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;">
            {{ $users->links() }}
        </div>
    @endif
</div>

{{-- Модалка блокировки --}}
<div id="blockModal" class="modal" style="display:none;">
    <div class="modal-overlay" onclick="closeBlockModal()"></div>
    <div class="modal-box">
        <h3>Заблокировать пользователя</h3>
        <p id="blockModalName" style="color:#6b7280;margin-bottom:16px;"></p>
        <form id="blockForm" method="POST">
            @csrf
            <div class="form-group">
                <label for="blockReason">Причина (необязательно)</label>
                <textarea id="blockReason" name="reason" class="form-control" rows="3"
                          placeholder="Укажите причину блокировки..."></textarea>
            </div>
            <div style="display:flex;gap:8px;margin-top:16px;">
                <button type="submit" class="btn btn-danger">Заблокировать</button>
                <button type="button" class="btn btn-outline" onclick="closeBlockModal()">Отмена</button>
            </div>
        </form>
    </div>
</div>

<script>
function showBlockForm(userId, userName) {
    const base = '{{ url("admin/users") }}';
    document.getElementById('blockForm').action = base + '/' + userId + '/block';
    document.getElementById('blockModalName').textContent = userName;
    document.getElementById('blockReason').value = '';
    document.getElementById('blockModal').style.display = 'flex';
}
function closeBlockModal() {
    document.getElementById('blockModal').style.display = 'none';
}
</script>

<style>
.admin-table { width:100%; border-collapse:collapse; }
.admin-table th,
.admin-table td { padding:10px 12px; text-align:left; border-bottom:1px solid #e5e7eb; font-size:.9rem; vertical-align:middle; }
.admin-table th { background:#f9fafb; font-weight:600; }
.row--blocked td { color:#9ca3af; }

.badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:.75rem; font-weight:600; }
.badge-success { background:#d1fae5; color:#065f46; }
.badge-error   { background:#fee2e2; color:#991b1b; }
.badge-warning { background:#fef3c7; color:#92400e; }

.btn-danger { background:#ef4444; color:#fff; border:none; }
.btn-danger:hover { background:#dc2626; }

.modal { position:fixed; inset:0; z-index:1000; align-items:center; justify-content:center; }
.modal-overlay { position:absolute; inset:0; background:rgba(0,0,0,.4); }
.modal-box { position:relative; background:#fff; border-radius:10px; padding:28px; width:100%; max-width:420px; box-shadow:0 8px 32px rgba(0,0,0,.16); }
</style>
@endsection
