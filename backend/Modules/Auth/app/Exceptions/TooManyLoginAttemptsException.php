<?php

namespace Modules\Auth\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TooManyLoginAttemptsException extends Exception
{
    public function __construct(
        private readonly int $retryAfterSeconds,
    ) {
        parent::__construct('Muitas tentativas de login. Tente novamente mais tarde.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => 'TOO_MANY_ATTEMPTS',
                'message' => $this->getMessage(),
                'details' => ['retry_after' => $this->retryAfterSeconds],
            ],
        ], 429)->header('Retry-After', (string) $this->retryAfterSeconds);
    }
}
