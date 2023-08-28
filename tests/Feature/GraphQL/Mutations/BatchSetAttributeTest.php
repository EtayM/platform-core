<?php

namespace Enjin\Platform\Tests\Feature\GraphQL\Mutations;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Enjin\Platform\Enums\Global\TransactionState;
use Enjin\Platform\Models\Collection;
use Enjin\Platform\Models\Token;
use Enjin\Platform\Services\Database\WalletService;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Token\Encoder;
use Enjin\Platform\Services\Token\Encoders\Integer;
use Enjin\Platform\Support\Account;
use Enjin\Platform\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Tests\Support\MocksWebsocketClient;
use Faker\Generator;
use Illuminate\Database\Eloquent\Model;

class BatchSetAttributeTest extends TestCaseGraphQL
{
    use ArraySubsetAsserts;
    use MocksWebsocketClient;

    protected string $method = 'BatchSetAttribute';
    protected Codec $codec;
    protected string $defaultAccount;
    protected Model $collection;
    protected Model $token;
    protected Encoder $tokenIdEncoder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codec = new Codec();
        $walletService = new WalletService();

        $this->defaultAccount = Account::daemonPublicKey();
        $owner = $walletService->firstOrStore(['public_key' => $this->defaultAccount]);
        $this->collection = Collection::factory()->create([
            'owner_wallet_id' => $owner->id,
        ]);
        $this->token = Token::factory([
            'collection_id' => $this->collection->id,
        ])->create();
        $this->tokenIdEncoder = new Integer($this->token->token_chain_id);
    }

    // Happy Path
    public function test_it_can_skip_validation(): void
    {
        $encodedData = $this->codec->encode()->batchSetAttribute(
            $collectionId = random_int(1, 1000),
            null,
            $attributes = $this->randomAttributes(),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'attributes' => $attributes,
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
    }

    public function test_it_can_simulate(): void
    {
        $encodedData = $this->codec->encode()->batchSetAttribute(
            $collectionId = $this->collection->collection_chain_id,
            $this->tokenIdEncoder->encode(),
            $attributes = $this->randomAttributes(),
        );

        $this->mockFee($feeDetails = app(Generator::class)->fee_details());
        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => $attributes,
            'simulate' => true,
        ]);

        $this->assertIsNumeric($response['deposit']);
        $this->assertArraySubset([
            'id' => null,
            'method' => $this->method,
            'state' => TransactionState::PENDING->name,
            'encodedData' => $encodedData,
            'fee' => $feeDetails['fakeSum'],
            'wallet' => [
                'account' => [
                    'publicKey' => $this->defaultAccount,
                ],
            ],
        ], $response);
    }

    public function test_it_can_batch_set_attribute_on_token(): void
    {
        $encodedData = $this->codec->encode()->batchSetAttribute(
            $collectionId = $this->collection->collection_chain_id,
            $this->tokenIdEncoder->encode(),
            $attributes = $this->randomAttributes(),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => $attributes,
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

    public function test_it_can_batch_set_attribute_on_collection(): void
    {
        $encodedData = $this->codec->encode()->batchSetAttribute(
            $collectionId = $this->collection->collection_chain_id,
            $tokenId = null,
            $attributes = $this->randomAttributes(),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'attributes' => $attributes,
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

    public function test_it_can_batch_set_attribute_on_collection_with_continue_on_failure(): void
    {
        $encodedData = $this->codec->encode()->batchSetAttribute(
            $collectionId = $this->collection->collection_chain_id,
            $tokenId = null,
            $attributes = $this->randomAttributes(),
            true,
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'attributes' => $attributes,
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

    public function test_it_can_batch_set_attribute_on_token_max_amount(): void
    {
        $encodedData = $this->codec->encode()->batchSetAttribute(
            $collectionId = $this->collection->collection_chain_id,
            $tokenId = $this->tokenIdEncoder->encode(),
            $attributes = $this->randomAttributes(20, 20),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => $attributes,
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

    public function test_it_can_batch_set_attribute_on_collection_max_amount(): void
    {
        $encodedData = $this->codec->encode()->batchSetAttribute(
            $collectionId = $this->collection->collection_chain_id,
            $tokenId = null,
            $attributes = $this->randomAttributes(20, 20),
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'attributes' => $attributes,
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

    public function test_it_can_batch_set_attribute_with_encoded_token(): void
    {
        $encodedData = $this->codec->encode()->batchSetAttribute(
            $collectionId = $this->collection->collection_chain_id,
            $this->tokenIdEncoder->encode(),
            $attributes = $this->randomAttributes(),
            true,
        );

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => $attributes,
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

    // Exception Path
    public function test_it_will_fail_with_no_args(): void
    {
        $response = $this->graphql($this->method, [], true);

        $this->assertStringContainsString(
            'Variable "$collectionId" of required type "BigInt!" was not provided',
            $response['error']
        );
    }

    public function test_it_will_fail_with_no_collection_id(): void
    {
        $response = $this->graphql($this->method, [
            'tokenId' => $this->token->token_chain_id,
            'attributes' => $this->randomAttributes(),
        ], true);

        $this->assertStringContainsString(
            'Variable "$collectionId" of required type "BigInt!" was not provided',
            $response['error']
        );
    }

    public function test_it_will_fail_with_collection_id_equals_null(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => null,
            'tokenId' => $this->token->token_chain_id,
            'attributes' => $this->randomAttributes(),
        ], true);

        $this->assertStringContainsString(
            'Variable "$collectionId" of non-null type "BigInt!" must not be null',
            $response['error']
        );
    }

    public function test_it_will_fail_with_collection_that_doesnt_exists(): void
    {
        Collection::where('collection_chain_id', '=', $collectionId = fake()->randomNumber())?->delete();

        $response = $this->graphql($this->method, [
            'collectionId' => $collectionId,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => $this->randomAttributes(),
        ], true);

        $this->assertArraySubset(
            [
                'collectionId' => [
                    0 => 'The selected collection id is invalid.',
                ],
            ],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_collection_id(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => 123,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => $this->randomAttributes(),
        ], true);

        $this->assertArraySubset(
            [
                'collectionId' => [
                    0 => 'The selected collection id is invalid.',
                ],
                'tokenId' => [
                    0 => 'The token id does not exist in the specified collection.',
                ],
            ],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_token_id(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(123),
            'attributes' => $this->randomAttributes(),
        ], true);

        $this->assertArraySubset(
            [
                'tokenId' => [
                    0 => 'The token id does not exist in the specified collection.',
                ],
            ],
            $response['error']
        );
    }

    public function test_it_will_fail_with_token_that_doesnt_exists(): void
    {
        Token::where('token_chain_id', '=', $tokenId = fake()->randomNumber())?->delete();

        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable($tokenId),
            'attributes' => $this->randomAttributes(),
        ], true);

        $this->assertArraySubset(
            [
                'tokenId' => [
                    0 => 'The token id does not exist in the specified collection.',
                ],
            ],
            $response['error']
        );
    }

    public function test_it_will_fail_with_no_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
        ], true);

        $this->assertStringContainsString(
            'Variable "$attributes" of required type "[AttributeInput!]!" was not provided',
            $response['error']
        );
    }

    public function test_it_will_fail_with_null_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => null,
        ], true);

        $this->assertStringContainsString(
            'Variable "$attributes" of non-null type "[AttributeInput!]!" must not be null',
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => 'invalid',
        ], true);

        $this->assertStringContainsString(
            'Variable "$attributes" got invalid value "invalid"; Expected type "AttributeInput" to be an object',
            $response['error']
        );
    }

    public function test_it_will_fail_with_empty_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => [],
        ], true);

        $this->assertArraySubset(
            [
                'attributes' => [
                    0 => 'The attributes field must have at least 1 items.',
                ],
            ],
            $response['error']
        );
    }

    public function test_it_will_fail_with_missing_key_in_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => [
                [
                    'value' => 'abc',
                ],
            ],
        ], true);

        $this->assertStringContainsString(
            'abc',
            $response['error']
        );
    }

    public function test_it_fail_with_simulate_invalid(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => [
                [
                    'key' => 'test',
                    'value' => 'abc',
                ],
            ],
            'simulate' => 'invalid',
        ], true);

        $this->assertStringContainsString(
            'Variable "$simulate" got invalid value "invalid"',
            $response['error']
        );
    }

    public function test_it_will_fail_with_missing_value_in_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => [
                [
                    'key' => 'abc',
                ],
            ],
        ], true);

        $this->assertStringContainsString(
            'abc',
            $response['error']
        );
    }

    public function test_it_will_fail_with_null_key_in_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => [
                [
                    'key' => null,
                    'value' => 'abc',
                ],
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$attributes" got invalid value null at "attributes[0].key"; Expected non-nullable type "String!" not to be null',
            $response['error']
        );
    }

    public function test_it_will_fail_with_null_value_in_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => [
                [
                    'key' => 'abc',
                    'value' => null,
                ],
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$attributes" got invalid value null at "attributes[0].value"; Expected non-nullable type "String!" not to be null',
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_key_in_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => [
                [
                    'key' => 123,
                    'value' => 'abc',
                ],
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$attributes" got invalid value 123 at "attributes[0].key"; String cannot represent a non string value',
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_value_in_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => [
                [
                    'key' => 'abc',
                    'value' => 123,
                ],
            ],
        ], true);

        $this->assertStringContainsString(
            'Variable "$attributes" got invalid value 123 at "attributes[0].value"; String cannot represent a non string value',
            $response['error']
        );
    }

    public function test_it_will_fail_with_more_than_max_attributes(): void
    {
        $response = $this->graphql($this->method, [
            'collectionId' => $this->collection->collection_chain_id,
            'tokenId' => $this->tokenIdEncoder->toEncodable(),
            'attributes' => $this->randomAttributes(21, 21),
        ], true);

        $this->assertArraySubset(
            [
                'attributes' => [
                    0 => 'The attributes field must not have more than 20 items.',
                ],
            ],
            $response['error']
        );
    }

    public function test_it_will_fail_with_is_not_the_owner(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->graphql($this->method, [
            'collectionId' => $collection->collection_chain_id,
            'attributes' => $this->randomAttributes(),
        ], true);

        $this->assertArraySubset(
            ['collectionId' => ['The collection id provided is not owned by you.']],
            $response['error']
        );
    }

    protected function randomAttributes(?int $min = 1, ?int $max = 10): array
    {
        return collect(range(1, mt_rand($min, $max)))->map(
            fn () => [
                'key' => fake()->word,
                'value' => fake()->word,
            ]
        )->toArray();
    }
}
