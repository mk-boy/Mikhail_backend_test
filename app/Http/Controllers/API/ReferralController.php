<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referral\AttachReferralRequest;
use App\Models\Master;
use App\Services\Referral\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function __construct(private readonly ReferralService $referrals)
    {
    }

    public function attach(AttachReferralRequest $request): JsonResponse
    {
        $master = $this->currentMaster($request);

        if (!$master) {
            return $this->unauthorized();
        }

        $referral = $this->referrals->registerReferral($master, $request->validated('code'));

        if (!$referral) {
            return response()->json([
                'message' => 'Код не найден или нельзя закрепиться за самим собой',
            ], 422);
        }

        return response()->json([
            'data' => [
                'id' => $referral->id,
                'referrer_master_id' => $referral->referrer_master_id,
                'referred_master_id' => $referral->referred_master_id,
                'status' => $referral->status,
                'attached_at' => $referral->created_at->toIso8601String(),
            ],
        ], $referral->wasRecentlyCreated ? 201 : 200);
    }

    public function my(Request $request): JsonResponse
    {
        $master = $this->currentMaster($request);

        if (!$master) {
            return $this->unauthorized();
        }

        return response()->json([
            'data' => $this->referrals->referredList($master),
        ]);
    }

    public function earnings(Request $request): JsonResponse
    {
        $master = $this->currentMaster($request);

        if (!$master) {
            return $this->unauthorized();
        }

        return response()->json([
            'data' => $this->referrals->earningsSummary($master),
        ]);
    }

    private function currentMaster(Request $request): ?Master
    {
        $master = $request->attributes->get('current_master');

        return $master instanceof Master ? $master : null;
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json(['message' => 'Не найден мастер по X-Master-Id'], 401);
    }
}
