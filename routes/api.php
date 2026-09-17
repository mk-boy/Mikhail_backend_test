<?php

use App\Http\Controllers\API\ReferralController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
|
| Текущий мастер приходит в заголовке X-Master-Id и уже разложен
| в атрибуты запроса middleware'ом ResolveCurrentMaster:
|
|     $master = $request->attributes->get('current_master');
|
*/

Route::get('/ping', fn () => ['ok' => true]);

Route::prefix('referrals')->controller(ReferralController::class)->group(function () {
    Route::post('/attach', 'attach');
    Route::get('/my', 'my');
    Route::get('/earnings', 'earnings');
});
