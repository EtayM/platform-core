<?php

namespace Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\MultiTokens;

use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Support\Account;
use Illuminate\Support\Arr;

class TokenMutated extends Event implements PolkadartEvent
{
    public readonly ?string $extrinsicIndex;
    public readonly string $module;
    public readonly string $name;
    public readonly string $collectionId;
    public readonly string $tokenId;
    public readonly ?bool $listingForbidden;
    public readonly string $behaviorMutation;
    public readonly bool $isCurrency;
    public readonly ?string $beneficiary;
    public readonly ?string $percentage;

    public static function fromChain(array $data): self
    {
        $self = new self();

        $self->extrinsicIndex = Arr::get($data, 'phase.ApplyExtrinsic');
        $self->module = array_key_first(Arr::get($data, 'event'));
        $self->name = array_key_first(Arr::get($data, 'event.' . $self->module));
        $self->collectionId = $self->getValue($data, ['collection_id', 'T::CollectionId']);
        $self->tokenId = $self->getValue($data, ['token_id', 'T::TokenId']);
        $self->listingForbidden = $self->getValue($data, ['mutation.listing_forbidden.SomeMutation', 'T::TokenMutation.listing_forbidden.SomeMutation']);
        $self->behaviorMutation = is_string($behavior = $self->getValue($data, ['mutation.behavior', 'T::TokenMutation.behavior'])) ? $behavior : array_key_first($behavior);
        $self->isCurrency = $self->getValue($data, ['mutation.behavior.SomeMutation.Some', 'T::TokenMutation.behavior.SomeMutation.Some']) === 'IsCurrency';
        $self->beneficiary = Account::parseAccount($self->getValue($data, ['mutation.behavior.SomeMutation.Some.HasRoyalty.beneficiary', 'T::TokenMutation.behavior.SomeMutation.HasRoyalty.beneficiary']));
        $self->percentage = $self->getValue($data, ['mutation.behavior.SomeMutation.Some.HasRoyalty.percentage', 'T::TokenMutation.behavior.SomeMutation.HasRoyalty.percentage']);

        if ($self->getValue($data, ['T::TokenMutation.metadata.SomeMutation']) != null) {
            throw new \Exception('Metadata is not null');
        }

        return $self;
    }

    public function getParams(): array
    {
        return [
            ['type' => 'collection_id', 'value' => $this->collectionId],
            ['type' => 'token_id', 'value' => $this->tokenId],
            ['type' => 'listing_forbidden', 'value' => $this->listingForbidden],
            ['type' => 'behavior_mutation', 'value' => $this->behaviorMutation],
            ['type' => 'is_currency', 'value' => $this->isCurrency],
            ['type' => 'beneficiary', 'value' => $this->beneficiary],
            ['type' => 'percentage', 'value' => $this->percentage],
        ];
    }
}

/* Example 1
  [▼
    "phase" => array:1 [▼
      "ApplyExtrinsic" => 2
    ]
    "event" => array:1 [▼
      "MultiTokens" => array:1 [▼
        "TokenMutated" => array:3 [▼
          "T::CollectionId" => "77160"
          "T::TokenId" => "1"
          "T::TokenMutation" => array:4 [▼
            "behavior" => array:1 [▼
              "SomeMutation" => array:1 [▼
                "HasRoyalty" => array:2 [▼
                  "beneficiary" => array:32 [▼
                    0 => 212
                    1 => 53
                    2 => 147
                    3 => 199
                    4 => 21
                    5 => 253
                    6 => 211
                    7 => 28
                    8 => 97
                    9 => 20
                    10 => 26
                    11 => 189
                    12 => 4
                    13 => 169
                    14 => 159
                    15 => 214
                    16 => 130
                    17 => 44
                    18 => 133
                    19 => 88
                    20 => 133
                    21 => 76
                    22 => 205
                    23 => 227
                    24 => 154
                    25 => 86
                    26 => 132
                    27 => 231
                    28 => 165
                    29 => 109
                    30 => 162
                    31 => 125
                  ]
                  "percentage" => 10000000
                ]
              ]
            ]
            "listing_forbidden" => array:1 [▼
              "SomeMutation" => true
            ]
            "anyone_can_infuse" => array:1 [▼
              "SomeMutation" => true
            ]
            "name" => array:1 [▼
              "SomeMutation" => array:8 [▼
                0 => 76
                1 => 101
                2 => 32
                3 => 84
                4 => 111
                5 => 107
                6 => 101
                7 => 110
              ]
            ]
          ]
        ]
      ]
    ]
    "topics" => []
  ]
 */
