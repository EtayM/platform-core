<?php

namespace Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Mutations;

use Closure;
use Enjin\Platform\GraphQL\Base\Mutation;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\HasEncodableTokenId;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\InPrimarySubstrateSchema;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasSkippableRules;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasTokenIdFieldRules;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasIdempotencyField;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Interfaces\PlatformGraphQlMutation;
use Enjin\Platform\Models\Substrate\BurnParams;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MaxTokenBalance;
use Enjin\Platform\Rules\MinBigInt;
use Enjin\Platform\Services\Database\TransactionService;
use Enjin\Platform\Services\Serialization\Interfaces\SerializationServiceInterface;
use Enjin\Platform\Support\Hex;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;
use Rebing\GraphQL\Support\Facades\GraphQL;

class BurnMutation extends Mutation implements PlatformBlockchainTransaction, PlatformGraphQlMutation
{
    use InPrimarySubstrateSchema;
    use HasEncodableTokenId;
    use HasIdempotencyField;
    use HasTokenIdFieldRules;
    use HasSkippableRules;

    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Burn',
            'description' => __('enjin-platform::mutation.burn.description'),
        ];
    }

    /**
     * Get the mutation's return type.
     */
    public function type(): Type
    {
        return GraphQL::type('Transaction!');
    }

    /**
     * Get the mutation's arguments definition.
     */
    public function args(): array
    {
        return [
            'collectionId' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform::mutation.burn.args.collectionId'),
            ],
            'params' => [
                'type' => GraphQL::type('BurnParamsInput!'),
                'description' => __('enjin-platform::mutation.burn.args.params'),
            ],
            ...$this->getIdempotencyField(),
            ...$this->getSkipValidationField(),
        ];
    }

    /**
     * Resolve the mutation's request.
     */
    public function resolve(
        $root,
        array $args,
        $context,
        ResolveInfo $resolveInfo,
        Closure $getSelectFields,
        SerializationServiceInterface $serializationService,
        TransactionService $transactionService,
    ): mixed {
        $args['params']['tokenId'] = $this->encodeTokenId($args['params']);
        unset($args['params']['encodeTokenId']);
        $encodedData = $serializationService->encode($this->getMethodName(), [
            'collectionId' => $args['collectionId'],
            'params' => new BurnParams(...$args['params']),
        ]);

        return Transaction::lazyLoadSelectFields(
            $transactionService->store([
                'method' => $this->getMutationName(),
                'encoded_data' => $encodedData,
                'idempotency_key' => $args['idempotencyKey'] ?? Str::uuid()->toString(),
            ]),
            $resolveInfo
        );
    }

    /**
     * Get the mutation's validation rules.
     */
    protected function rulesWithValidation(array $args): array
    {
        return [
            'collectionId' => ['exists:collections,collection_chain_id'],
            'params.amount' => [new MinBigInt(1), new MaxTokenBalance()],
            ...$this->getTokenFieldRulesExist('params'),
        ];
    }

    /**
     * Get the mutation's validation rules withoud DB rules.
     */
    protected function rulesWithoutValidation(array $args): array
    {
        return [
            'collectionId' => [new MinBigInt(2000), new MaxBigInt(Hex::MAX_UINT128)],
            'params.amount' => [new MinBigInt(1), new MaxBigInt(Hex::MAX_UINT128)],
            ...$this->getTokenFieldRules('params'),
        ];
    }
}
