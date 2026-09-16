<?php

namespace Database\Seeders;

use App\Models\Master;
use App\Models\Payment;
use App\Models\Referral;
use Illuminate\Database\Seeder;

/**
 * Демо-данные.
 *
 * Маша — реферер, у неё код MASHA10. По нему пришли четыре мастера
 * с разной историей платежей. Лена пришла сама, без кода.
 *
 * Платежи создаются через модель, поэтому обработчик платежей
 * отрабатывает так же, как в бою.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $masha = Master::create(['name' => 'Маша', 'referral_code' => 'MASHA10']);
        $lena = Master::create(['name' => 'Лена', 'referral_code' => 'LENA77']);

        $ira = Master::create(['name' => 'Ира', 'referral_code' => 'IRA31']);
        $olya = Master::create(['name' => 'Оля', 'referral_code' => 'OLYA22']);
        $katya = Master::create(['name' => 'Катя', 'referral_code' => 'KATYA05']);
        $dasha = Master::create(['name' => 'Даша', 'referral_code' => 'DASHA64']);

        foreach ([$ira, $olya, $katya, $dasha] as $referred) {
            Referral::create([
                'referrer_master_id' => $masha->id,
                'referred_master_id' => $referred->id,
                'status' => Referral::STATUS_PENDING,
            ]);
        }

        // Ира: оплатила картой, потом продлила.
        Payment::create(['master_id' => $ira->id, 'amount' => 3000, 'type' => Payment::TYPE_CARD]);
        Payment::create(['master_id' => $ira->id, 'amount' => 3000, 'type' => Payment::TYPE_CARD]);

        // Оля: сидит на промокоде, денег не платила.
        Payment::create(['master_id' => $olya->id, 'amount' => 0, 'type' => Payment::TYPE_PROMO]);

        // Катя: пробный период, платежей нет.
        Payment::create(['master_id' => $katya->id, 'amount' => 0, 'type' => Payment::TYPE_TRIAL]);

        // Даша: неудачное списание на 0, следом настоящая оплата.
        Payment::create(['master_id' => $dasha->id, 'amount' => 0, 'type' => Payment::TYPE_CARD]);
        Payment::create(['master_id' => $dasha->id, 'amount' => 2000, 'type' => Payment::TYPE_CARD]);

        // Лена пришла без реферального кода.
        Payment::create(['master_id' => $lena->id, 'amount' => 3000, 'type' => Payment::TYPE_SBP]);
    }
}
