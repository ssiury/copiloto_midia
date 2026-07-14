<?php

use App\Models\User;

test('registro cria usuário com user_type free', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Iury Sousa',
        'email' => 'iury@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.email', 'iury@example.com')
        ->assertJsonPath('data.user_type', 'free');

    $this->assertDatabaseHas('users', [
        'email' => 'iury@example.com',
        'user_type' => 'free',
    ]);

    $user = User::where('email', 'iury@example.com')->firstOrFail();
    expect($user->password)->not->toBe('password123');
});

test('registro não vaza senha na resposta', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Iury Sousa',
        'email' => 'iury@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated();
    expect($response->json('data'))->not->toHaveKey('password');
});
