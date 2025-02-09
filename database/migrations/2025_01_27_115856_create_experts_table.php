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
        Schema::create('experts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('image')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->string('lat')->nullable();
            $table->string('lon')->nullable();
            $table->integer('experience')->nullable();
            $table->string('otp',10)->nullable();
            $table->string('price')->nullable();
            $table->string('user_id')->nullable();
            $table->decimal('amount', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experts');
    }
};
