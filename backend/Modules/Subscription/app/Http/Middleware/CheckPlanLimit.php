<?php

namespace Modules\Subscription\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Subscription\Contracts\PlanServiceInterface;
use Modules\Subscription\Exceptions\PlanLimitReachedException;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanLimit
{
    public function __construct(
        private readonly PlanServiceInterface $planService,
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
