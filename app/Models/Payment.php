<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Платёж мастера за подписку.
 *
 * Типы:
 *  - card, sbp   — настоящие деньги
 *  - promo       — оплата промокодом (amount = 0)
 *  - trial       — пробный период (amount = 0)
 */
class Payment extends Model
{
    use HasFactory;

    public const TYPE_CARD = 'card';
    public const TYPE_SBP = 'sbp';
    public const TYPE_PROMO = 'promo';
    public const TYPE_TRIAL = 'trial';

    protected $fillable = [
        'master_id',
        'amount',
        'type',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class);
    }

    /** Платёж настоящими деньгами. */
    public static function isMonetary(self $payment): bool
    {
        return in_array($payment->type, [self::TYPE_CARD, self::TYPE_SBP], true)
            && $payment->amount > 0;
    }

    /** Только денежные платежи. */
    public function scopeMonetary(Builder $query): Builder
    {
        return $query->whereIn('type', [self::TYPE_CARD, self::TYPE_SBP]);
    }
}
