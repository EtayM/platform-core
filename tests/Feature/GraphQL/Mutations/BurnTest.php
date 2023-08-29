<?php

namespace Enjin\Platform\Tests\Feature\GraphQL\Mutations;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Enjin\Platform\Enums\Global\TransactionState;
use Enjin\Platform\Events\Global\TransactionCreated;
use Enjin\Platform\Models\Collection;
use Enjin\Platform\Models\Substrate\BurnParams;
use Enjin\Platform\Models\Token;
use Enjin\Platform\Models\TokenAccount;
use Enjin\Platform\Services\Database\WalletService;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Token\Encoder;
use Enjin\Platform\Services\Token\Encoders\Integer;
use Enjin\Platform\Support\Account;
use Enjin\Platform\Support\Hex;
use Enjin\Platform\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Tests\Support\MocksWebsocketClient;
use Faker\Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

class BurnTest extends TestCaseGraphQL
{
    use ArraySubsetAsserts;
    use MocksWebsocketClient;

    protected $method = 'Burn';
    protected Codec $codec;
    protected string $defaultAccount;
    protected Model $wallet;
    protected Model $collection;
    protected Model $token;
    protected Encoder $tokenIdEncoder;
    protected Model $tokenAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codec = new Codec();
        $walletService = new WalletService();
        $this->defaultAccount = Account::daemonPublicKey();
        $this->wallet = $walletService->firstOrStore(['public_key' => $this->defaultAccount]);

        $this->tokenAccount = TokenAccount::factory([
            'wallet_id' => $this->wallet,
        ])->create();

        $this->token = Token::find($this->tokenAccount->token_id);
        $this->tokenIdEncoder = new Integer($this->token->token_chain_id);
        $this->collection = Collection::find($this->tokenAccount->collection_id);
    }

    // Happy Path

    public function test_can_skip_validation(): void
    {
        $encodedData = $this->codec->encode()->burn(
            $collectionId = random_int(2000, 3000),
            new BurnParams(
                tokenId: $this->tokenIdEncoder->encode(),
                amount: $amount = 1,
            ),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => $amount,
            ],
            'skipValidation' => true,
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_burn_a_token_with_default_values_using_adapter(): void
    {
        $encodedData = $this->codec->encode()->burn(
            $collectionId = $this->collection->collection_chain_id,
            new BurnParams(
                tokenId: $this->tokenIdEncoder->encode(),
                amount: $amount = fake()->numberBetween(0, $this->tokenAccount->balance),
            ),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => $amount,
            ],
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);
    }

    public function test_it_can_simulate(): void
    {
        $encodedData = $this->codec->encode()->burn(
            $collectionId = $this->collection->collection_chain_id,
            new BurnParams(
                tokenId: $this->tokenIdEncoder->encode(),
                amount: $amount = fake()->numberBetween(0, $this->tokenAccount->balance),
            ),
        );

        $this->mockFee($feeDetails = app(Generator::class)->fee_details());
        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => $amount,
            ],
            'simulate' => true,
        ]);

        $this->assertArraySubset([
            'id' => null,
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'fee' => $feeDetails['fakeSum'],
            'deposit' => null,
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_can_burn_a_token_with_default_values(): void
    {
        $encodedData = $this->codec->encode()->burn(
            $collectionId = $this->collection->collection_chain_id,
            new BurnParams(
                tokenId: $this->tokenIdEncoder->encode(),
                amount: $amount = fake()->numberBetween(0, $this->tokenAccount->balance),
            ),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => $amount,
            ],
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_burn_a_token_with_keepalive(): void
    {
        $encodedData = $this->codec->encode()->burn(
            $collectionId = $this->collection->collection_chain_id,
            new BurnParams(
                tokenId: $this->tokenIdEncoder->encode(),
                amount: $amount = fake()->numberBetween(0, $this->tokenAccount->balance),
                keepAlive: $keepAlive = fake()->boolean(),
            ),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => $amount,
                'keepAlive' => $keepAlive,
            ],
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_burn_a_token_with_remove_token_storage(): void
    {
        $encodedData = $this->codec->encode()->burn(
            $collectionId = $this->collection->collection_chain_id,
            new BurnParams(
                tokenId: $this->tokenIdEncoder->encode(),
                amount: $amount = fake()->numberBetween(0, $this->tokenAccount->balance),
                removeTokenStorage: $removeTokenStorage = fake()->boolean(),
            ),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => $amount,
                'removeTokenStorage' => $removeTokenStorage,
            ],
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);


        $encodedData = $this->codec->encode()->burn(
            $collectionId = $this->collection->collection_chain_id,
            new BurnParams(
                tokenId: $this->tokenIdEncoder->encode(),
                amount: 0,
                removeTokenStorage: true,
            ),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => 0,
                'removeTokenStorage' => true,
            ],
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_burn_a_token_with_all_args(): void
    {
        $encodedData = $this->codec->encode()->burn(
            $collectionId = $this->collection->collection_chain_id,
            $params = new BurnParams(
                tokenId: $this->tokenIdEncoder->encode(),
                amount: fake()->numberBetween(0, $this->tokenAccount->balance),
                keepAlive: fake()->boolean(),
                removeTokenStorage: fake()->boolean(),
            ),
        );

        $params = $params->toArray();
        $params['tokenId'] = $this->tokenIdEncoder->toEncodable();

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => $params,
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_burn_a_token_with_bigint_tokenid(): void
    {
        $collection = Collection::factory([
            'collection_chain_id' => fake()->numberBetween(2000),
        ])->create();

        $token = Token::factory([
            'collection_id' => $collection,
            'token_chain_id' => Hex::MAX_UINT128,
        ])->create();

        $tokenAccount = TokenAccount::factory([
            'collection_id' => $collection,
            'token_id' => $token,
            'wallet_id' => $this->wallet,
        ])->create();

        $encodedData = $this->codec->encode()->burn(
            $collectionId = $collection->collection_chain_id,
            $params = new BurnParams(
                tokenId: $this->tokenIdEncoder->encode($token->token_chain_id),
                amount: fake()->numberBetween(0, $tokenAccount->balance),
            ),
        );

        $params = $params->toArray();
        $params['tokenId'] = $this->tokenIdEncoder->toEncodable($token->token_chain_id);

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => $params,
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_burn_a_token_with_bigint_amount(): void
    {
        $collection = Collection::factory([
            'collection_chain_id' => fake()->numberBetween(2000),
        ])->create();

        $token = Token::factory([
            'collection_id' => $collection,
            'token_chain_id' => Hex::MAX_UINT128,
        ])->create();

        TokenAccount::factory([
            'collection_id' => $collection,
            'token_id' => $token,
            'wallet_id' => $this->wallet,
            'balance' => $balance = Hex::MAX_UINT128,
        ])->create();

        $encodedData = $this->codec->encode()->burn(
            $collectionId = $collection->collection_chain_id,
            $params = new BurnParams(
                tokenId: $token->token_chain_id,
                amount: $balance,
            ),
        );

        $params = $params->toArray();
        $params['tokenId'] = $this->tokenIdEncoder->toEncodable($token->token_chain_id);

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => $params,
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    // Exception Path

    public function test_it_will_fail_collection_id_that_doesnt_exists(): void
    {
        Collection::where('collection_chain_id', '=', $collectionId = fake()->numberBetween(2000))?->delete();

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => fake()->numberBetween(0, $this->tokenAccount->balance),
            ],
        ], true);

        $this->assertArraySubset(
            ['collectionId' => ['The selected collection id is invalid.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_token_id_that_doesnt_exists(): void
    {
        Token::where('token_chain_id', '=', $tokenId = fake()->numberBetween())?->delete();

        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable($tokenId),
                'amount' => fake()->numberBetween(0, $this->tokenAccount->balance),
            ],
        ], true);

        $this->assertArraySubset(
            ['params.tokenId' => ['The params.token id does not exist in the specified collection.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_invalid_collection_id(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => 'not_valid',
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => fake()->numberBetween(0, $this->tokenAccount->balance),
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$collectionId" got invalid value "not_valid"; Cannot represent following value as uint256',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_invalid_token_id(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [
                'tokenId' => 'not_valid',
                'amount' => fake()->numberBetween(0, $this->tokenAccount->balance),
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$params" got invalid value "not_valid" at "params.tokenId"; Expected type "EncodableTokenIdInput" to be an object',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_negative_collection_id(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => -1,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => fake()->numberBetween(0, $this->tokenAccount->balance),
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$collectionId" got invalid value -1; Cannot represent following value as uint256',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_negative_token_id(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [
                'tokenId' => -1,
                'amount' => fake()->numberBetween(0, $this->tokenAccount->balance),
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$params" got invalid value -1 at "params.tokenId"; Expected type "EncodableTokenIdInput" to be an object',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_negative_amount(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => -1,
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$params" got invalid value -1 at "params.amount"; Cannot represent following value as uint256',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_zero_amount(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => 0,
            ],
        ], true);

        $this->assertArraySubset(
            ['params.amount' => ['The params.amount is too small, the minimum value it can be is 1.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_no_collection_id(): void
    {
        $response = $this->graphql($this->method, [
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => fake()->numberBetween(0, $this->tokenAccount->balance),
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$collectionId" of required type "BigInt!" was not provided.',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_no_token_id(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [
                'amount' => fake()->numberBetween(0, $this->tokenAccount->balance),
            ],
        ], true);

        $this->assertStringContainsString(
            'Field "tokenId" of required type "EncodableTokenIdInput!" was not provided',
            $response['errors'][0]['message']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_no_amount(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
            ],
        ], true);

        $this->assertStringContainsString(
            'Field "amount" of required type "BigInt!" was not provided',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_invalid_keepalive(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => fake()->numberBetween(0, $this->tokenAccount->balance),
                'keepAlive' => 'invalid',
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$params" got invalid value "invalid" at "params.keepAlive"; Boolean cannot represent a non boolean value',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_invalid_removetokenstorage(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => fake()->numberBetween(0, $this->tokenAccount->balance),
                'removeTokenStorage' => 'invalid',
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$params" got invalid value "invalid" at "params.removeTokenStorage"; Boolean cannot represent a non boolean value',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_empty_params(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [],
        ], true);

        $this->assertStringContainsString(
            'Variable "$params" got invalid value []; Field "tokenId" of required type "EncodableTokenIdInput!" was not provide',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_when_trying_to_burn_more_than_balance(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'params' => [
                'tokenId' => $this->tokenIdEncoder->toEncodable(),
                'amount' => fake()->numberBetween($this->tokenAccount->balance),
            ],
        ], true);

        $this->assertArraySubset(
            ['params.amount' => ['The params.amount is invalid, the amount provided is bigger than the token account balance.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }
}
