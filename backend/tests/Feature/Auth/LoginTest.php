<?php

use App\Models\User;

test('login retorna token válido', function () {
    User::factory()->create([
        'email' => 'iury@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'iury@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.user.email', 'iury@example.com')
        ->assertJsonStructure(['data' => ['token', 'user']]);

    expect($response->json('data.token'))->not->toBeEmpty();
});

test('login com credenciais erradas retorna 401 com formato de erro padrão', function () {
    User::factory()->create([
        'email' => 'iury@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'iury@example.com',
        'password' => 'senha-errada',
    ]);

    $response->assertStatus(401)
        ->assertJsonStructure(['error' => ['code', 'message', 'details']])
        ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
});
