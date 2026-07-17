<?php

namespace Modules\Subscription\Data;

final readonly class PaymentData
{
    public function __construct(
        public string $amountCents,
        public string $currency,
        public string $gatewayReference,
        public string $status,
    ) {
    }
}
