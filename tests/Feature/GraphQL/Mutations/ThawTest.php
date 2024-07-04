<?php

namespace Enjin\Platform\Tests\Feature\GraphQL\Mutations;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Enjin\Platform\Enums\Global\TransactionState;
use Enjin\Platform\Enums\Substrate\FreezeType;
use Enjin\Platform\Events\Global\TransactionCreated;
use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Mutations\ThawMutation;
use Enjin\Platform\Models\Collection;
use Enjin\Platform\Models\CollectionAccount;
use Enjin\Platform\Models\Substrate\FreezeTypeParams;
use Enjin\Platform\Models\Token;
use Enjin\Platform\Models\TokenAccount;
use Enjin\Platform\Models\Wallet;
use Enjin\Platform\Rules\IsCollectionOwner;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Token\Encoder;
use Enjin\Platform\Services\Token\Encoders\Integer;
use Enjin\Platform\Support\Account;
use Enjin\Platform\Support\Hex;
use Enjin\Platform\Support\SS58Address;
use Enjin\Platform\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Tests\Support\MocksWebsocketClient;
use Facades\Enjin\Platform\Services\Blockchain\Implementations\Substrate;
use Faker\Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

class ThawTest extends TestCaseGraphQL
{
    use ArraySubsetAsserts;
    use MocksWebsocketClient;

    protected string $method = 'Thaw';
    protected Codec $codec;
    protected Model $wallet;
    protected Model $collection;
    protected Model $collectionAccount;
    protected Model $token;
    protected Encoder $tokenIdEncoder;
    protected Model $tokenAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->codec = new Codec();
        $this->wallet = Account::daemon();
        $this->collection = Collection::factory()->create(['owner_wallet_id' => $this->wallet]);
        $this->token = Token::factory(['collection_id' => $this->collection])->create();
        $this->tokenAccount = TokenAccount::factory([
            'wallet_id' => $this->wallet,
            'collection_id' => $this->collection->id,
            'token_id' => $this->token->id,
        ])->create();
        $this->collectionAccount = CollectionAccount::factory([
            'collection_id' => $this->collection,
            'wallet_id' => $this->wallet,
            'account_count' => 1,
        ])->create();
        $this->tokenIdEncoder = new Integer($this->token->token_chain_id);
    }

    // Happy Path
    public function test_it_can_skip_validation(): void
    {
        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId = random_int(1, 1000),
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::COLLECTION
            ),
        ));

        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
            'skipValidation' => true,
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => null,
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_it_can_simulate(): void
    {
        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId = $this->collection->collection_chain_id,
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::COLLECTION
            ),
        ));

        $this->mockFee($feeDetails = app(Generator::class)->fee_details());
        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
            'simulate' => true,
        ]);

        $this->assertArraySubset([
            'id' => null,
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'fee' => $feeDetails['fakeSum'],
            'deposit' => null,
            'wallet' => null,
        ], $response);

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_can_bypass_ownership(): void
    {
        $signingWallet = Wallet::factory()->create();
        $collection = Collection::factory()->create(['owner_wallet_id' => $signingWallet]);
        Token::factory([
            'collection_id' => $collection,
        ])->create();

        $response = $this->graphql($this->method, $params = [
            'freezeType' => FreezeType::COLLECTION->name,
            'collectionId' => $collection->collection_chain_id,
            'nonce' => fake()->numberBetween(),
        ], true);
        $this->assertEquals(
            ['collectionId' => ['The collection id provided is not owned by you.']],
            $response['error']
        );

        IsCollectionOwner::bypass();
        $response = $this->graphql($this->method, $params);
        $this->assertNotEmpty($response);
        IsCollectionOwner::unBypass();
    }

    public function test_can_thaw_a_collection(): void
    {
        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId = $this->collection->collection_chain_id,
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::COLLECTION
            ),
        ));

        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
            'nonce' => $nonce = fake()->numberBetween(),
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'signingPayload' => Substrate::getSigningPayload($encodedData, [
                'nonce' => $nonce,
                'tip' => '0',
            ]),
            'wallet' => null,
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_thaw_a_collection_with_signing_account(): void
    {
        $signingWallet = Wallet::factory([
            'public_key' => $signingAccount = app(Generator::class)->public_key(),
        ])->create();
        $collection = Collection::factory()->create(['owner_wallet_id' => $signingWallet]);
        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId = $collection->collection_chain_id,
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::COLLECTION
            ),
        ));

        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
            'signingAccount' => SS58Address::encode($signingAccount),
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $signingAccount,
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

    public function test_can_thaw_a_collection_with_public_key_signing_account(): void
    {
        $signingWallet = Wallet::factory([
            'public_key' => $signingAccount = app(Generator::class)->public_key(),
        ])->create();
        $collection = Collection::factory()->create(['owner_wallet_id' => $signingWallet]);
        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId = $collection->collection_chain_id,
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::COLLECTION
            ),
        ));

        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
            'signingAccount' => $signingAccount,
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => [
                'account' => [
                    'publicKey' => $signingAccount,
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

    public function test_can_thaw_a_big_int_collection(): void
    {
        Collection::where('collection_chain_id', Hex::MAX_UINT128)->delete();
        $collection = Collection::factory([
            'collection_chain_id' => $collectionId = Hex::MAX_UINT128,
            'owner_wallet_id' => $this->wallet,
        ])->create();
        CollectionAccount::factory([
            'collection_id' => $collection,
            'wallet_id' => $this->wallet,
            'account_count' => 1,
        ])->create();

        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId,
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::COLLECTION
            ),
        ));

        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => null,
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_thaw_a_collection_account(): void
    {
        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId = $this->collection->collection_chain_id,
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::COLLECTION_ACCOUNT,
                account: $account = $this->wallet->public_key,
            ),
        ));

        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
            'collectionAccount' => SS58Address::encode($account),
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => null,
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_thaw_a_token_using_adapter(): void
    {
        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId = $this->collection->collection_chain_id,
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::TOKEN,
                token: $this->tokenIdEncoder->encode(),
            ),
        ));

        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => null,
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);
    }

    public function test_can_thaw_a_token(): void
    {
        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId = $this->collection->collection_chain_id,
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::TOKEN,
                token: $this->tokenIdEncoder->encode(),
            ),
        ));

        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => null,
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_thaw_a_big_int_token(): void
    {
        $collection = Collection::factory(['owner_wallet_id' => $this->wallet])->create();
        Token::factory([
            'collection_id' => $collection,
            'token_chain_id' => $tokenId = Hex::MAX_UINT128,
        ])->create();
        CollectionAccount::factory([
            'collection_id' => $collection,
            'wallet_id' => $this->wallet,
            'account_count' => 1,
        ])->create();

        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId = $collection->collection_chain_id,
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::TOKEN,
                token: $this->tokenIdEncoder->encode($tokenId),
            ),
        ));

        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
            'tokenId' => $this->tokenIdEncoder->toEncodable($tokenId),
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => null,
        ], $response);

        $this->assertDatabaseHas('transactions', [
            'id' => $response['id'],
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encoded_data' => $encodedData,
        ]);

        Event::assertDispatched(TransactionCreated::class);
    }

    public function test_can_thaw_a_token_account(): void
    {
        $encodedData = TransactionSerializer::encode($this->method, ThawMutation::getEncodableParams(
            collectionId: $collectionId = $this->collection->collection_chain_id,
            thawParams: new FreezeTypeParams(
                type: $freezeType = FreezeType::TOKEN_ACCOUNT,
                token: $this->tokenIdEncoder->encode(),
                account: $account = $this->wallet->public_key,
            ),
        ));

        $response = $this->graphql($this->method, [
            'freezeType' => $freezeType->name,
            'collectionId' => $collectionId,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'tokenAccount' => SS58Address::encode($account),
        ]);

        $this->assertArraySubset([
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'wallet' => null,
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

    public function test_it_will_fail_with_thaw_type_non_existent(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => 'ASSET',
            'collectionId' => $this->collection->collection_chain_id,
        ], true);

        $this->assertStringContainsString(
            'Variable "$freezeType" got invalid value "ASSET"; Value "ASSET" does not exist in "FreezeType" enum',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_collection_id_non_existent(): void
    {
        Collection::where('collection_chain_id', '=', $collectionId = fake()->numberBetween(2000))?->delete();

        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION->name,
            'collectionId' => $collectionId,
        ], true);

        $this->assertArraySubset(
            ['collectionId' => ['The selected collection id is invalid.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_invalid_collection_id(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION->name,
            'collectionId' => 'invalid',
        ], true);

        $this->assertStringContainsString(
            'Variable "$collectionId" got invalid value "invalid"; Cannot represent following value as uint256',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_null_collection_id(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION->name,
            'collectionId' => null,
        ], true);

        $this->assertStringContainsString(
            'Variable "$collectionId" of non-null type "BigInt!" must not be null.',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_no_collection_id(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION->name,
        ], true);

        $this->assertStringContainsString(
            'Variable "$collectionId" of required type "BigInt!" was not provided.',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_null_token_id(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => null,
        ], true);

        $this->assertArraySubset(
            ['tokenId' => ['The token id field is required.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_no_token_id(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN->name,
            'collectionId' => $this->collection->collection_chain_id,
        ], true);

        $this->assertArraySubset(
            ['tokenId' => ['The token id field is required.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_invalid_token_id(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => 'invalid',
        ], true);

        $this->assertStringContainsString(
            'Variable "$tokenId" got invalid value "invalid"; Expected type "EncodableTokenIdInput" to be an object',
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_token_id_non_existent(): void
    {
        Token::where('token_chain_id', '=', $tokenId = fake()->numberBetween())?->delete();

        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable($tokenId),
        ], true);

        $this->assertArraySubset(
            ['tokenId' => ['The token id does not exist in the specified collection.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_null_collection_account(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION_ACCOUNT->name,
            'collectionId' => $this->collection->collection_chain_id,
            'collectionAccount' => null,
        ], true);

        $this->assertArraySubset(
            ['collectionAccount' => ['The collection account field is required.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_no_collection_account(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION_ACCOUNT->name,
            'collectionId' => $this->collection->collection_chain_id,
        ], true);

        $this->assertArraySubset(
            ['collectionAccount' => ['The collection account field is required.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_invalid_collection_account(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION_ACCOUNT->name,
            'collectionId' => $this->collection->collection_chain_id,
            'collectionAccount' => 'invalid',
        ], true);

        $this->assertArraySubset(
            ['collectionAccount' => ['The collection account is not a valid substrate account.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_collection_account_non_existent(): void
    {
        Wallet::where('public_key', '=', $address = app(Generator::class)->public_key())?->delete();

        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION_ACCOUNT->name,
            'collectionId' => $collectionId = $this->collection->collection_chain_id,
            'collectionAccount' => $address,
        ], true);

        $this->assertArraySubset(
            ['collectionAccount' => ["Could not find a collection account for {$address} at collection {$collectionId}."]],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_token_account_non_existent(): void
    {
        Wallet::where('public_key', '=', $address = app(Generator::class)->public_key())?->delete();

        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN_ACCOUNT->name,
            'collectionId' => $collectionId = $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'tokenAccount' => $address,
        ], true);

        $this->assertArraySubset(
            ['tokenAccount' => ["Could not find a token account for {$address} at collection {$collectionId} and token {$this->token->token_chain_id}."]],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_null_token_account(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN_ACCOUNT->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'tokenAccount' => null,
        ], true);

        $this->assertArraySubset(
            ['tokenAccount' => ['The token account field is required.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_no_token_account(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN_ACCOUNT->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
        ], true);

        $this->assertArraySubset(
            ['tokenAccount' => ['The token account field is required.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_will_fail_with_invalid_token_account(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN_ACCOUNT->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'tokenAccount' => 'invalid',
        ], true);

        $this->assertArraySubset(
            ['tokenAccount' => ['The token account is not a valid substrate account.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_cant_pass_token_id_when_freezing_collection(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
        ], true);

        $this->assertArraySubset(
            ['tokenId' => ['The token id field is prohibited.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_cant_pass_collection_account_when_freezing_collection(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION->name,
            'collectionId' => $this->collection->collection_chain_id,
            'collectionAccount' => $this->wallet->public_key,
        ], true);

        $this->assertArraySubset(
            ['collectionAccount' => ['The collection account field is prohibited.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_cant_pass_token_account_when_freezing_collection(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenAccount' => $this->wallet->public_key,
        ], true);

        $this->assertArraySubset(
            ['tokenAccount' => ['The token account field is prohibited.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_cant_pass_token_account_when_freezing_collection_account(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION_ACCOUNT->name,
            'collectionId' => $this->collection->collection_chain_id,
            'collectionAccount' => $this->wallet->public_key,
            'tokenAccount' => $this->wallet->public_key,
        ], true);

        $this->assertArraySubset(
            ['tokenAccount' => ['The token account field is prohibited.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_cant_pass_token_id_when_freezing_collection_account(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::COLLECTION_ACCOUNT->name,
            'collectionId' => $this->collection->collection_chain_id,
            'collectionAccount' => $this->wallet->public_key,
            'tokenId' => $this->tokenIdEncoder->toEncodable($this->token->token_chain_address),
        ], true);

        $this->assertArraySubset(
            ['tokenId' => ['The token id field is prohibited.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_cant_pass_token_account_when_freezing_token(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'tokenAccount' => $this->wallet->public_key,
        ], true);

        $this->assertArraySubset(
            ['tokenAccount' => ['The token account field is prohibited.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_cant_pass_collection_account_when_freezing_token(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'collectionAccount' => $this->wallet->public_key,
        ], true);

        $this->assertArraySubset(
            ['collectionAccount' => ['The collection account field is prohibited.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }

    public function test_it_cant_pass_collection_account_when_freezing_token_account(): void
    {
        $response = $this->graphql($this->method, [
            'freezeType' => FreezeType::TOKEN_ACCOUNT->name,
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'collectionAccount' => $this->wallet->public_key,
            'tokenAccount' => $this->wallet->public_key,
        ], true);

        $this->assertArraySubset(
            ['collectionAccount' => ['The collection account field is prohibited.']],
            $response['error']
        );

        Event::assertNotDispatched(TransactionCreated::class);
    }
}
