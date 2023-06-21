<?php

namespace Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace;

use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Illuminate\Support\Arr;

class ListingCancelled implements PolkadartEvent
{
    public readonly string $extrinsicIndex;
    public readonly string $module;
    public readonly string $name;
    public readonly string $listingId;

    public static function fromChain(array $data): self
    {
        $self = new self();
        $self->extrinsicIndex = Arr::get($data, 'phase.ApplyExtrinsic');
        $self->module = array_key_first(Arr::get($data, 'event'));
        $self->name = array_key_first(Arr::get($data, 'event.' . $self->module));
        $self->listingId = Arr::get($data, 'event.Marketplace.ListingCancelled.listing_id');

        return $self;
    }

    public function getPallet(): string
    {
        return $this->module;
    }

    public function getParams(): array
    {
        return [
            ['type' => 'listing_id', 'value' => $this->listingId],
        ];
    }
}

/* Example 1
    {
        "phase": {
            "ApplyExtrinsic": 25
        },
        "event": {
            "Marketplace": {
                "ListingCancelled": {
                    "listing_id": "23f6172d569c15f67ad4a9ba7207e237f48cc0f01ce1ddd12121a66eb30d2444"
                }
            }
        },
        "topics": []
    },
*/
