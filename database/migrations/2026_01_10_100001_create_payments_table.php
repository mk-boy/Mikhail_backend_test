<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('master_id')->constrained('masters')->cascadeOnDelete();
            $table->unsignedInteger('amount')->default(0);
            $table->string('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
