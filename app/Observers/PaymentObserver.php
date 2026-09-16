<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Referral;
use App\Models\ReferralEarning;
use App\Services\Referral\ReferralService;

class PaymentObserver
{
    public function __construct(private ReferralService $referrals)
    {
    }

    /**
     * Обработка нового платежа.
     */
    public function created(Payment $payment): void
    {
        if (!Payment::isMonetary($payment)) {
            return;
        }

        $referral = Referral::where('referred_master_id', $payment->master_id)
            ->where('status', Referral::STATUS_PENDING)
            ->first();

        if (empty($referral)) {
            return;
        }

        // Вознаграждение выдаётся один раз — с первого денежного платежа.
        $monetaryCount = Payment::where('master_id', $payment->master_id)
            ->monetary()
            ->count();

        if ($monetaryCount > 1) {
            return;
        }

        ReferralEarning::create([
            'referrer_master_id' => $referral->referrer_master_id,
            'referred_master_id' => $referral->referred_master_id,
            'referral_id' => $referral->id,
            'payment_id' => $payment->id,
            'payment_amount' => $payment->amount,
            'amount' => $this->referrals->rewardAmount((int) $payment->amount),
            'percent' => (int) config('referral.percent'),
            'status' => ReferralEarning::STATUS_PENDING,
        ]);

        $referral->update(['status' => Referral::STATUS_REWARDED]);
    }
}
