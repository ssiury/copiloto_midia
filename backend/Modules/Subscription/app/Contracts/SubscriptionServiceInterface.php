<?php

namespace Modules\Subscription\Contracts;

use App\Models\User;
use Modules\Subscription\Data\Payment;
use Modules\Subscription\Data\PaymentData;
use Modules\Subscription\Models\Plan;
use Modules\Subscription\Models\UserSubscription;

interface SubscriptionServiceInterface
{
    public function createSubscription(User $user, Plan $plan): UserSubscription;

    public function cancelSubscription(UserSubscription $subscription): UserSubscription;

    public function renewSubscription(UserSubscription $subscription): UserSubscription;

    public function changePlan(UserSubscription $subscription, Plan $newPlan): UserSubscription;

    /**
     * Assinatura já pensada para o payload de um webhook de gateway
     * (Stripe, Mercado Pago, Asaas ou Pagar.me) — ver docs/architecture.md.
     * Nenhum gateway está integrado ainda; ver Modules\Subscription\Services\SubscriptionService.
     */
    public function registerPayment(UserSubscription $subscription, PaymentData $payment): Payment;
}
