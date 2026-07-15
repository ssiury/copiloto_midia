<?php

namespace Modules\Subscription\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Subscription\Http\Resources\SubscriptionResource;
use Modules\Subscription\Services\PlanService;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly PlanService $planService,
    ) {
    }

    public function me(Request $request): JsonResponse
    {
        $summary = $this->planService->summaryFor($request->user());

        abort_if(! $summary, 404);

        return (new SubscriptionResource($summary))
            ->additional(['meta' => (object) []])
            ->response();
    }
}
