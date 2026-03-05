<div>
    {{-- Фильтры --}}
    <div class="filters-form">
        <div class="filters-row">
            <select wire:model.live="filterRole" class="form-control">
                <option value="">Любая свободная роль</option>
                <option value="therapist">Нужен терапевт</option>
                <option value="client">Нужен клиент</option>
                <option value="observer">Нужен наблюдатель</option>
            </select>

            <input type="date" wire:model.live="filterDateFrom" class="form-control"
                   placeholder="С даты" style="max-width: 160px;">

            <input type="date" wire:model.live="filterDateTo" class="form-control"
                   placeholder="По дату" style="max-width: 160px;">

            @if($filterRole || $filterDateFrom || $filterDateTo)
                <button wire:click="resetFilters" class="btn btn-outline btn-sm">Сбросить</button>
            @endif
        </div>
    </div>

    {{-- Индикатор загрузки --}}
    <div wire:loading class="loading-bar"></div>

    @if($slotItems->isEmpty())
        <div class="empty-state">
            <p>Нет открытых слотов по выбранным фильтрам.</p>
        </div>
    @else
        <div class="slots-grid">
            @foreach($slotItems as $slot)
                @php
                    $takenRoles = $slot->activeParticipants->pluck('role')->map(fn($r) => $r->value ?? $r)->toArray();
                    $roleLabels = ['therapist' => 'Терапевт', 'client' => 'Клиент', 'observer' => 'Наблюдатель'];
                    $roleColors = ['therapist' => 'role-badge--therapist', 'client' => 'role-badge--client', 'observer' => 'role-badge--observer'];
                @endphp
                <div class="slot-card card">
                    <div class="slot-card-header">
                        <span class="slot-date">{{ $slot->starts_at->format('d.m.Y') }}</span>
                        <span class="slot-time">{{ $slot->starts_at->format('H:i') }}</span>
                        @if($slot->blind_mode)
                            <span class="badge badge-outline" title="Терапевт не видит инструкцию клиента">🙈 Слепой</span>
                        @endif
                    </div>

                    <h3 class="slot-task-title">
                        <a href="{{ route('triads.slots.show', $slot) }}">{{ $slot->task->title }}</a>
                    </h3>

                    <p class="slot-duration text-light">{{ $slot->task->duration_minutes }} мин · Автор: {{ $slot->creator->name }}</p>

                    {{-- Роли --}}
                    <div class="slot-roles">
                        @foreach(['therapist', 'client', 'observer'] as $role)
                            @php $taken = in_array($role, $takenRoles); @endphp
                            <div class="slot-role {{ $taken ? 'slot-role--taken' : 'slot-role--free' }}">
                                <span class="role-badge {{ $roleColors[$role] }}">{{ $roleLabels[$role] }}</span>
                                @if($taken)
                                    @php $p = $slot->activeParticipants->first(fn($p) => ($p->role->value ?? $p->role) === $role); @endphp
                                    <span class="slot-role-user">{{ $p?->user->name }}</span>
                                @else
                                    <span class="slot-role-free">свободно</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('triads.slots.show', $slot) }}" class="btn btn-primary btn-sm" style="margin-top: auto;">
                        Подробнее
                    </a>
                </div>
            @endforeach
        </div>

        <div class="pagination-wrapper">
            {{ $slotItems->links() }}
        </div>
    @endif
</div>
