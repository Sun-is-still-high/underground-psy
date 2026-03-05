<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('slots')->cascadeOnDelete();
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invitee_id')->constrained('users')->cascadeOnDelete();
            $table->string('proposed_role'); // therapist, client, observer
            $table->string('status')->default('pending'); // pending, accepted, declined
            $table->timestamps();

            $table->index('slot_id');
            $table->index('invitee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_invitations');
    }
};
