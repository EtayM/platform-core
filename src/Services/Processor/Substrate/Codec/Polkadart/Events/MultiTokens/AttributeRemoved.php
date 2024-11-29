<?php

namespace Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\MultiTokens;

use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Illuminate\Support\Arr;

class AttributeRemoved extends Event implements PolkadartEvent
{
    public readonly ?string $extrinsicIndex;
    public readonly string $module;
    public readonly string $name;
    public readonly string $collectionId;
    public readonly ?string $tokenId;
    public readonly string $key;

    #[\Override]
    public static function fromChain(array $data): self
    {
        $self = new self();

        $self->extrinsicIndex = Arr::get($data, 'phase.ApplyExtrinsic');
        $self->module = array_key_first(Arr::get($data, 'event'));
        $self->name = array_key_first(Arr::get($data, 'event.' . $self->module));
        $self->collectionId = $self->getValue($data, 'T::CollectionId');
        $self->tokenId = $self->getValue($data, 'Option<T::TokenId>');
        $self->key = (is_string($value = $self->getValue($data, 'T::AttributeKey')) ? $value : HexConverter::bytesToHex($value));

        return $self;
    }

    #[\Override]
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
    array:3 [▼
      "phase" => array:1 [▼
        "ApplyExtrinsic" => 2
      ]
      "event" => array:1 [▼
        "MultiTokens" => array:1 [▼
          "AttributeRemoved" => array:3 [▼
            "T::CollectionId" => "77159"
            "Option<T::TokenId>" => null
            "T::AttributeKey" => array:4 [▼
              0 => 110
              1 => 97
              2 => 109
              3 => 101
            ]
          ]
        ]
      ]
      "topics" => []
    ]
 */
