<?php

namespace Modules\Subscription\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanLimitReachedException extends Exception
{
    public function __construct(
        private readonly string $resource,
    ) {
        parent::__construct("Limite do plano atingido para o recurso \"{$resource}\".");
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'PLAN_LIMIT_REACHED',
                'message' => $this->getMessage(),
                'details' => ['resource' => $this->resource],
            ],
        ], 403);
    }
}
