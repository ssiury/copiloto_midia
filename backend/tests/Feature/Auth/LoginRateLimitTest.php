<?php

use App\Models\User;

test('rate limit bloqueia login após 5 tentativas', function () {
    User::factory()->create([
        'email' => 'iury@example.com',
        'password' => 'password123',
    ]);

    $payload = ['email' => 'iury@example.com', 'password' => 'senha-errada'];

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', $payload)->assertStatus(401);
    }

    $response = $this->postJson('/api/v1/auth/login', $payload);

    $response->assertStatus(429)
        ->assertJsonPath('error.code', 'TOO_MANY_ATTEMPTS');
});
