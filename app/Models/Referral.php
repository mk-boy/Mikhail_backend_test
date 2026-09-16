<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Привязка приведённого мастера к тому, кто его привёл.
 *
 * Программы:
 *  - master_invite — мастер пригласил мастера по своему коду
 *  - influencer    — мастер пришёл по промокоду инфлюенсера
 */
class Referral extends Model
{
    use HasFactory;

    public const PROGRAM_MASTER_INVITE = 'master_invite';
    public const PROGRAM_INFLUENCER = 'influencer';

    public const STATUS_PENDING = 'pending';
    public const STATUS_REWARDED = 'rewarded';

    protected $fillable = [
        'referrer_master_id',
        'referred_master_id',
        'status',
    ];

    /** Кто привёл. */
    public function referrerMaster(): BelongsTo
    {
        return $this->belongsTo(Master::class, 'referrer_master_id');
    }

    /** Кого привели. */
    public function referredMaster(): BelongsTo
    {
        return $this->belongsTo(Master::class, 'referred_master_id');
    }

    /**
     * Активные привязки — те, что ещё могут принести вознаграждение.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REWARDED);
    }
}
