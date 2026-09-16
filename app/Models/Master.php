<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Мастер — пользователь сервиса.
 *
 * У каждого мастера есть личный реферальный код, по которому
 * за ним закрепляются приведённые им мастера.
 */
class Master extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'referral_code',
    ];

    /** Все платежи мастера. */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Привязки мастеров, которых привёл этот мастер. */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_master_id');
    }

    /** Начисления, полученные этим мастером за рефералов. */
    public function referralEarnings(): HasMany
    {
        return $this->hasMany(ReferralEarning::class, 'referrer_master_id');
    }

    /**
     * Мастер оплатил подписку.
     */
    public function isPaid(): bool
    {
        return $this->payments()->exists();
    }
}
