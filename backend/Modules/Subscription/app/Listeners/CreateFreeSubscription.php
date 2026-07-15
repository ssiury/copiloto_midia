<?php

namespace Modules\Subscription\Listeners;

use Modules\Auth\Events\UserRegistered;
use Modules\Subscription\Contracts\SubscriptionServiceInterface;
use Modules\Subscription\Models\Plan;

class CreateFreeSubscription
{
    public function __construct(
        private readonly SubscriptionServiceInterface $subscriptions,
    ) {
    }

    public function handle(UserRegistered $event): void
    {
        $freePlan = Plan::where('slug', 'free')->firstOrFail();

        $this->subscriptions->createSubscription($event->user, $freePlan);
    }
}
