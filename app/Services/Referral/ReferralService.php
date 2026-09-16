<?php

namespace App\Services\Referral;

use App\Models\Master;
use App\Models\Referral;

class ReferralService
{
    /**
     * Регистрирует реферала: создаёт привязку приведённого мастера
     * к владельцу кода и начисляет рефереру вознаграждение.
     *
     * Возвращает null, если код не найден или мастер пытается
     * закрепить сам себя.
     */
    public function registerReferral(Master $referred, string $code): ?Referral
    {
        $referrer = Master::where('referral_code', $code)->first();

        if (empty($referrer) || $referrer->id === $referred->id) {
            return null;
        }

        return Referral::firstOrCreate(
            [
                'referred_master_id' => $referred->id,
            ],
            [
                'referrer_master_id' => $referrer->id,
                'program' => Referral::PROGRAM_MASTER_INVITE,
                'status' => Referral::STATUS_PENDING,
            ]
        );
    }

    /**
     * Сумма вознаграждения реферера с одного платежа реферала.
     *
     * Считается как процент от суммы платежа, процент задан
     * в config/referral.php.
     */
    public function rewardAmount(int $paymentAmount): int
    {
        $percent = (int) config('referral.percent');

        return (int) round($paymentAmount * $percent);
    }
}
