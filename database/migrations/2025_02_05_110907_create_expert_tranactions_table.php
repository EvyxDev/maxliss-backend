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
        Schema::create('expert_tranactions', function (Blueprint $table) {
            $table->id();
            $table->integer('expert_id')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->decimal('amount', 8, 2)->default(0);
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expert_tranactions');
    }
};
