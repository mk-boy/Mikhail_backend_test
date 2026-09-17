<?php

use App\Models\ReferralEarning;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_earnings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('referrer_master_id')->index()->constrained('masters')->cascadeOnDelete();
            $table->foreignId('referred_master_id')->index()->constrained('masters')->cascadeOnDelete();
            $table->foreignId('referral_id')->index()->constrained('referrals')->cascadeOnDelete();
            $table->foreignId('payment_id')->index()->constrained('payments')->cascadeOnDelete();
            $table->unsignedInteger('payment_amount')->default(0);
            $table->unsignedInteger('amount')->default(0);
            $table->unsignedInteger('percent')->default(0);
            $table->string('status')->default(ReferralEarning::STATUS_PENDING);
        });
    }

    public function down(): void
    {
        Schema::table('referral_earnings', function (Blueprint $table) {
            $table->dropIndex(['referrer_master_id']);
            $table->dropIndex(['referred_master_id']);
            $table->dropIndex(['referral_id']);
            $table->dropIndex(['payment_id']);

            $table->dropForeign(['referrer_master_id']);
            $table->dropForeign(['referred_master_id']);
            $table->dropForeign(['referral_id']);
            $table->dropForeign(['payment_id']);
        });

        Schema::dropIfExists('referral_earnings');
    }
};
