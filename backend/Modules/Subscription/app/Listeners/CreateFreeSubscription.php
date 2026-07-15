<?php

namespace Modules\Subscription\Listeners;

use Modules\Auth\Events\UserRegistered;
use Modules\Subscription\Models\Plan;
use Modules\Subscription\Models\UserSubscription;

class CreateFreeSubscription
{
    public function handle(UserRegistered $event): void
    {
        $freePlan = Plan::where('slug', 'free')->firstOrFail();

        UserSubscription::create([
            'user_id' => $event->user->id,
            'plan_id' => $freePlan->id,
            'status' => 'active',
            'started_at' => now(),
        ]);
    }
}
