<?php

namespace App\Livewire\Triads;

use App\Models\Slot;
use App\Models\SlotMessage;
use App\Models\SlotParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SlotChat extends Component
{
    public Slot $slot;
    public string $body = '';
    public bool $isParticipant = false;

    public function mount(Slot $slot): void
    {
        $this->slot = $slot;
        $this->isParticipant = SlotParticipant::where('slot_id', $slot->id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->exists();
    }

    public function send(): void
    {
        if (!$this->isParticipant) return;

        $this->validate([
            'body' => 'required|string|max:1000',
        ]);

        SlotMessage::create([
            'slot_id'    => $this->slot->id,
            'user_id'    => Auth::id(),
            'body'       => $this->body,
            'created_at' => now(),
        ]);

        $this->body = '';
    }

    public function render()
    {
        $messages = SlotMessage::with('user')
            ->where('slot_id', $this->slot->id)
            ->orderBy('created_at')
            ->get();

        return view('livewire.triads.slot-chat', compact('messages'));
    }
}
