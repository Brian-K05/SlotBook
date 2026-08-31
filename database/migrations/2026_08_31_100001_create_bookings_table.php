<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('status', 20);
            $table->boolean('paid')->default(false);
            $table->timestamps();

            $table->index(['slot_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
