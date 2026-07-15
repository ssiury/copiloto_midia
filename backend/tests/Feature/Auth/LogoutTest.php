<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('logout revoga o token e chamada subsequente com o token antigo falha', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout');

    $logoutResponse->assertOk();

    // O guard "sanctum" memoiza o usuário resolvido durante o ciclo de vida
    // da aplicação de teste; força nova resolução para refletir o token revogado.
    Auth::forgetGuards();

    $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me');

    $meResponse->assertStatus(401);
});
