<?php

namespace App\Livewire\Triads;

use App\Enums\SlotStatus;
use App\Models\Slot;
use Livewire\Component;
use Livewire\WithPagination;

class SlotList extends Component
{
    use WithPagination;

    public string $filterRole     = '';
    public string $filterDateFrom = '';
    public string $filterDateTo   = '';

    protected $queryString = [
        'filterRole'     => ['except' => '', 'as' => 'role'],
        'filterDateFrom' => ['except' => '', 'as' => 'from'],
        'filterDateTo'   => ['except' => '', 'as' => 'to'],
    ];

    public function updatedFilterRole(): void     { $this->resetPage(); }
    public function updatedFilterDateFrom(): void { $this->resetPage(); }
    public function updatedFilterDateTo(): void   { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->filterRole     = '';
        $this->filterDateFrom = '';
        $this->filterDateTo   = '';
        $this->resetPage();
    }

    public function render()
    {
        $slotItems = Slot::with(['task', 'creator', 'activeParticipants.user'])
            ->where('visibility', 'public')
            ->where('status', SlotStatus::Open)
            ->where('starts_at', '>', now()->addHour()) // скрываем просроченные по дедлайну
            ->when($this->filterRole, fn($q) => $q->whereDoesntHave('activeParticipants', function ($q) {
                $q->where('role', $this->filterRole)->where('status', 'active');
            }))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('starts_at', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo,   fn($q) => $q->whereDate('starts_at', '<=', $this->filterDateTo))
            ->orderBy('starts_at')
            ->paginate(12);

        return view('livewire.triads.slot-list', compact('slotItems'));
    }
}
