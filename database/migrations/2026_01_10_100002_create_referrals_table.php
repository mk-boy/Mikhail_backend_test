<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('referrer_master_id')->constrained('masters')->cascadeOnDelete();
            $table->foreignId('referred_master_id')->constrained('masters')->cascadeOnDelete();
            $table->string('program')->default('master_invite');
            $table->string('status')->default('pending');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
