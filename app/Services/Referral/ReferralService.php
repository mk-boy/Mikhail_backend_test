<?php

namespace App\Services\Referral;

use App\Models\Master;
use App\Models\Referral;
use App\Models\ReferralEarning;

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

    /**
     * Мастера, приведённые данным мастером: имя, дата привязки,
     * засчитан ли реферал и сколько по нему начислено.
     */
    public function referredList(Master $referrer): array
    {
        $referrals = $referrer->referrals()
            ->with('referredMaster')
            ->withSum('earnings as earned_amount', 'amount')
            ->orderBy('created_at')
            ->get();

        return $referrals->map(fn (Referral $referral) => [
            'master_id' => $referral->referred_master_id,
            'name' => $referral->referredMaster->name,
            'attached_at' => $referral->created_at->toIso8601String(),
            'rewarded' => $referral->status === Referral::STATUS_REWARDED,
            'earned_amount' => (int) ($referral->earned_amount ?? 0),
        ])->all();
    }

    /**
     * Сводка по начислениям реферера: всего, в ожидании, выплачено,
     * сколько рефералов засчитано.
     */
    public function earningsSummary(Master $referrer): array
    {
        $earnings = ReferralEarning::where('referrer_master_id', $referrer->id)->get(['amount', 'status']);

        $totalAmount = (int) $earnings->sum('amount');
        $pendingAmount = (int) $earnings->where('status', ReferralEarning::STATUS_PENDING)->sum('amount');
        $paidAmount = (int) $earnings->where('status', ReferralEarning::STATUS_PAID)->sum('amount');
        $rewardedReferralsCount = Referral::where('referrer_master_id', $referrer->id)
            ->where('status', Referral::STATUS_REWARDED)
            ->count();

        return [
            'total_amount' => $totalAmount,
            'pending_amount' => $pendingAmount,
            'paid_amount' => $paidAmount,
            'rewarded_referrals_count' => $rewardedReferralsCount,
        ];
    }
}
