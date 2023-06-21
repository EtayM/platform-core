<?php

namespace Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Queries;

use Closure;
use Enjin\Platform\GraphQL\Base\Query;
use Enjin\Platform\GraphQL\Middleware\ResolvePage;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\HasEncodableTokenId;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\InPrimarySubstrateSchema;
use Enjin\Platform\GraphQL\Types\Pagination\ConnectionInput;
use Enjin\Platform\Interfaces\PlatformGraphQlQuery;
use Enjin\Platform\Models\Token;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Rebing\GraphQL\Support\Facades\GraphQL;

class GetTokensQuery extends Query implements PlatformGraphQlQuery
{
    use InPrimarySubstrateSchema;
    use HasEncodableTokenId;

    protected $middleware = [
        ResolvePage::class,
    ];

    /**
     * Get the query's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'GetTokens',
            'description' => __('enjin-platform::query.get_tokens.description'),
        ];
    }

    /**
     * Get the query's return type.
     */
    public function type(): Type
    {
        return GraphQL::paginate('Token', 'TokenConnection');
    }

    /**
     * Get the query's arguments definition.
     */
    public function args(): array
    {
        return ConnectionInput::args([
            'collectionId' => [
                'type' => GraphQL::type('BigInt'),
                'description' => __('enjin-platform::query.get_tokens.args.collectionId'),
                'rules' => ['required_with:tokenIds', 'exists:Enjin\Platform\Models\Collection,collection_chain_id'],
            ],
            'tokenIds' => [
                'type' => GraphQL::type('[EncodableTokenIdInput]'),
                'description' => __('enjin-platform::query.get_tokens.args.tokenIds'),
                'rules' => ['nullable',  'bail', 'array', 'min:0', 'max:100', 'distinct'],
            ],
        ]);
    }

    /**
     * Resolve the query's request.
     */
    public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields): mixed
    {
        if (isset($args['tokenIds'])) {
            $args['tokenIds'] = collect($args['tokenIds'])->map(fn ($tokenId) => $this->encodeTokenId(['tokenId' => $tokenId]))->all();
        }

        return Token::loadSelectFields($resolveInfo, $this->name)
            ->when($collectionId = Arr::get($args, 'collectionId'), fn ($query) => $query->whereHas(
                'collection',
                fn ($query) => $query->where('collection_chain_id', $collectionId)
            ))
            ->when(Arr::get($args, 'tokenIds'), fn ($query) => $query->whereIn('token_chain_id', $args['tokenIds']))
            ->cursorPaginateWithTotalDesc('collection_id', $args['first']);
    }
}
