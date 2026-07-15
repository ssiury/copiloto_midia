<?php

namespace Modules\Subscription\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Subscription\Exceptions\PlanLimitReachedException;
use Modules\Subscription\Services\PlanService;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimit
{
    public function __construct(
        private readonly PlanService $planService,
    ) {
    }

    public function handle(Request $request, Closure $next, string $resource): Response
    {
        if ($this->planService->hasReachedLimit($request->user(), $resource)) {
            throw new PlanLimitReachedException($resource);
        }

        return $next($request);
    }
}
