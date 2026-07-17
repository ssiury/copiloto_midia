<?php

namespace Modules\Subscription\Data;

use Carbon\CarbonImmutable;

final readonly class Payment
{
    public function __construct(
        public string $subscriptionId,
        public string $amountCents,
        public string $currency,
        public string $gatewayReference,
        public string $status,
        public CarbonImmutable $registeredAt,
    ) {
    }
}
