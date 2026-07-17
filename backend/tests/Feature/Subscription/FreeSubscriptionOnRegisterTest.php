<?php

use App\Models\User;
use Modules\Subscription\Models\Plan;
use Modules\Subscription\Models\UserSubscription;

test('novo usuário recebe assinatura free ativa automaticamente', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Iury Sousa',
        'email' => 'iury@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated();

    $user = User::where('email', 'iury@example.com')->firstOrFail();
    $freePlan = Plan::where('slug', 'free')->firstOrFail();

    $this->assertDatabaseHas('user_subscriptions', [
        'user_id' => $user->id,
        'plan_id' => $freePlan->id,
        'status' => 'active',
    ]);

    $subscription = UserSubscription::where('user_id', $user->id)->firstOrFail();
    expect($subscription->status)->toBe('active');
});

test('dashboard de assinatura retorna o plano free vindo da api', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Iury Sousa',
        'email' => 'iury@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'iury@example.com')->firstOrFail();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/subscription/me');

    $response->assertOk()
        ->assertJsonPath('data.plan.slug', 'free')
        ->assertJsonPath('data.status', 'active');
});
