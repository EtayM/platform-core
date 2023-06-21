<?php

namespace Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\MultiTokens;

use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Illuminate\Support\Arr;

class AttributeRemoved implements PolkadartEvent
{
    public readonly string $extrinsicIndex;
    public readonly string $module;
    public readonly string $name;
    public readonly string $collectionId;
    public readonly ?string $tokenId;
    public readonly string $key;

    public static function fromChain(array $data): PolkadartEvent
    {
        $self = new self();
        $self->extrinsicIndex = Arr::get($data, 'phase.ApplyExtrinsic');
        $self->module = array_key_first(Arr::get($data, 'event'));
        $self->name = array_key_first(Arr::get($data, 'event.' . $self->module));
        $self->collectionId = Arr::get($data, 'event.MultiTokens.AttributeRemoved.collection_id');
        $self->tokenId = Arr::get($data, 'event.MultiTokens.AttributeRemoved.token_id.Some');
        $self->key = is_string($key = Arr::get($data, 'event.MultiTokens.AttributeRemoved.key')) ? $key : HexConverter::bytesToHex($key);

        return $self;
    }

    public function getPallet(): string
    {
        return $this->module;
    }

    public function getParams(): array
    {
        return [
            ['type' => 'collection_id', 'value' => $this->collectionId],
            ['type' => 'token_id', 'value' => $this->tokenId],
            ['type' => 'key', 'value' => $this->key],
        ];
    }
}

/* Example 1
    [
        "phase" => [
            "ApplyExtrinsic" => 2,
        ],
        "event" => [
            "MultiTokens" => [
                "AttributeRemoved" => [
                    "collection_id" => "9248",
                    "token_id" => [
                        "None" => null,
                    ],
                    "key" => [
                         0 => 110,
                         1 => 97,
                         ...,
                    ],
                ],
            ],
        ],
        "topics" => [],
    ]
 */
