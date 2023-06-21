<?php

namespace Enjin\Platform\GraphQL\Types\Substrate;

use Enjin\Platform\GraphQL\Types\Traits\InSubstrateSchema;
use Enjin\Platform\Interfaces\PlatformGraphQlType;
use Enjin\Platform\Models\TokenAccountNamedReserve;
use Enjin\Platform\Traits\HasSelectFields;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type;

class TokenAccountNamedReserveType extends Type implements PlatformGraphQlType
{
    use InSubstrateSchema;
    use HasSelectFields;

    /**
     * Get the type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'TokenAccountNamedReserve',
            'description' => __('enjin-platform::type.token_account_named_reserve.description'),
            'model' => TokenAccountNamedReserve::class,
        ];
    }

    /**
     * Get the type's fields definition.
     */
    public function fields(): array
    {
        return [
            // Properties
            'pallet' => [
                'type' => GraphQL::type('PalletIdentifier!'),
                'description' => __('enjin-platform::type.token_account_named_reserve.args.pallet'),
            ],
            'amount' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform::type.token_account_named_reserve.args.amount'),
            ],
        ];
    }
}
