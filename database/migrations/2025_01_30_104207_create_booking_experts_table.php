<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_experts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('expert_id')->nullable();
            $table->integer('expert_slot_id')->nullable();
            $table->integer('order_id')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_experts');
    }
};
