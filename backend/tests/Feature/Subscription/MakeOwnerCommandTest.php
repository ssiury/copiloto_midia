<?php

use App\Models\User;
use Modules\Subscription\Models\UserSubscription;

test('comando make:owner cria usuário com plano ilimitado e tipo owner', function () {
    $this->artisan('make:owner', ['email' => 'owner@example.com', 'password' => 'password123'])
        ->assertSuccessful();

    $user = User::where('email', 'owner@example.com')->firstOrFail();

    expect($user->user_type)->toBe('owner');

    $subscription = UserSubscription::where('user_id', $user->id)->firstOrFail();

    expect($subscription->status)->toBe('active')
        ->and($subscription->plan->slug)->toBe('owner')
        ->and($subscription->plan->is_unlimited)->toBeTrue();
});

test('comando make:owner falha se o e-mail já existir', function () {
    User::factory()->create(['email' => 'owner@example.com']);

    $this->artisan('make:owner', ['email' => 'owner@example.com', 'password' => 'password123'])
        ->assertFailed();
});
