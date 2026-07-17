<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentica rotas serviço-a-serviço (header X-Internal-Key) em vez de
 * sessão/token de usuário — ver docs/architecture.md, seção "Contrato de
 * API interna por módulo".
 */
class VerifyInternalApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('services.internal.key');

        if (! $expectedKey || ! hash_equals($expectedKey, (string) $request->header('X-Internal-Key'))) {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Chave de API interna inválida ou ausente.',
                    'details' => [],
                ],
            ], 401);
        }

        return $next($request);
    }
}
