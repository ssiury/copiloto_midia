<?php

use App\Models\User;
use Modules\Subscription\Models\Plan;
use Modules\Subscription\Models\UserSubscription;
use Modules\Subscription\Services\PlanService;

function subscribeUserToPlan(string $slug): User
{
    $user = User::factory()->create();
    $plan = Plan::where('slug', $slug)->firstOrFail();

    UserSubscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'started_at' => now(),
    ]);

    return $user;
}

test('hasReachedLimit retorna true quando free atinge o limite configurado', function () {
    $user = subscribeUserToPlan('free');

    Plan::where('slug', 'free')->firstOrFail()
        ->limits()
        ->where('resource', 'uploads')
        ->update(['limit' => 0]);

    $planService = app(PlanService::class);

    expect($planService->hasReachedLimit($user, 'uploads'))->toBeTrue()
        ->and($planService->hasReachedLimit($user, 'projects'))->toBeFalse();
});

test('isUnlimited retorna true para pro e owner', function () {
    $proUser = subscribeUserToPlan('pro');
    $ownerUser = subscribeUserToPlan('owner');
    $freeUser = subscribeUserToPlan('free');

    $planService = app(PlanService::class);

    expect($planService->isUnlimited($proUser))->toBeTrue()
        ->and($planService->isUnlimited($ownerUser))->toBeTrue()
        ->and($planService->isUnlimited($freeUser))->toBeFalse();
});
