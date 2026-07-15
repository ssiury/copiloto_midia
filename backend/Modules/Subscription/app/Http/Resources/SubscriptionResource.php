<?php

namespace Modules\Subscription\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->resource['status'],
            'started_at' => $this->resource['started_at'],
            'ends_at' => $this->resource['ends_at'],
            'plan' => [
                'name' => $this->resource['plan']->name,
                'slug' => $this->resource['plan']->slug,
                'is_paid' => $this->resource['plan']->is_paid,
                'is_unlimited' => $this->resource['plan']->is_unlimited,
            ],
            'limits' => $this->resource['limits'],
        ];
    }
}
