<?php

namespace Enjin\Platform\GraphQL\Schemas\Primary\Mutations;

use Closure;
use Enjin\Platform\Enums\Global\ModelType;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\HasContextSensitiveRules;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\InPrimarySchema;
use Enjin\Platform\Interfaces\PlatformGraphQlMutation;
use Enjin\Platform\Models\Syncable;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MinBigInt;
use Enjin\Platform\Support\Hex;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class RemoveFromTrackedMutation extends Mutation implements PlatformGraphQlMutation
{
    use HasContextSensitiveRules;
    use InPrimarySchema;

    public function __construct()
    {
        self::addContextSensitiveRule(ModelType::COLLECTION->name, [
            'chainIds.*' => [new MinBigInt(2000), new MaxBigInt(Hex::MAX_UINT128)],
        ]);
    }

    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'RemoveFromTracked',
            'description' => __('enjin-platform::mutation.remove_from_tracked.description'),
        ];
    }

    /**
     * Get the mutation's return type.
     */
    public function type(): Type
    {
        return GraphQL::type('Boolean!');
    }

    /**
     * Get the mutation's arguments definition.
     */
    public function args(): array
    {
        return [
            'type' => [
                'type' => GraphQL::type('ModelType!'),
                'description' => __('enjin-platform::mutation.add_to_tracked.args.model_type'),
            ],
            'chainIds' => [
                'type' => GraphQL::type('[BigInt!]!'),
                'description' => __('enjin-platform::mutation.add_to_tracked.args.chain_ids'),
            ],
        ];
    }

    /**
     * Resolve the mutation's request.
     */
    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields): mixed
    {
        Syncable::query()->whereIn(
            'syncable_id',
            $args['chainIds']
        )->where(
            'syncable_type',
            ModelType::getEnumCase($args['type'])->value
        )->get()->each(fn ($syncable) => $syncable->delete());

        return true;
    }

    /**
     * Get the validation rules.
     */
    protected function rules(array $args = []): array
    {
        return [
            'chainIds' => ['required', 'array', 'max:1000'],
            ...$this->getContextSensitiveRules($args['type']),
        ];
    }
}
