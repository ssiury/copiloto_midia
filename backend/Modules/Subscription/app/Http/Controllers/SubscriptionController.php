<?php

namespace Modules\Subscription\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Subscription\Contracts\PlanServiceInterface;
use Modules\Subscription\Http\Resources\SubscriptionResource;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly PlanServiceInterface $planService,
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
