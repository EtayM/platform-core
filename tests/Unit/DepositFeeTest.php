<?php

namespace Enjin\Platform\Tests\Unit;

use Codec\Utils;
use Enjin\Platform\Enums\Substrate\TransactionDeposit;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasTransactionDeposit;
use Enjin\Platform\Models\Collection;
use Enjin\Platform\Models\Token;
use Enjin\Platform\Tests\Support\MocksWebsocketClient;
use Enjin\Platform\Tests\TestCase;
use Facades\Enjin\Platform\Services\Blockchain\Implementations\Substrate;
use Faker\Generator;

class DepositFeeTest extends TestCase
{
    use MocksWebsocketClient;
    use HasTransactionDeposit;

    public function test_it_can_get_extrinsic_fee()
    {
        $extrinsic = '0x490284003a158a287b46acd830ee9a83d304a63569f8669968a20ea80720e338a565dd0901a2369c177101204aede6d1992240c436a05084f1b56321a0851ed346cb2783704fd10cbfd10a423f1f7ab321761de08c3927c082236ed433a979b19ee09ed7890064000a07003a158a287b46acd830ee9a83d304a63569f8669968a20ea80720e338a565dd0913000064a7b3b6e00d';
        $this->mockFee($feeDetails = app(Generator::class)->fee_details());
        $fee = Substrate::getFee($extrinsic);
        $totalFee = gmp_add(gmp_add(gmp_init($feeDetails['baseFee']), gmp_init($feeDetails['lenFee'])), gmp_init($feeDetails['adjustedWeightFee']));

        $this->assertEquals(gmp_strval($totalFee), $fee);
    }

    public function test_deposit_for_create_collection_with_empty_attributes()
    {
        $args = [
            'attributes' => [],
        ];

        $deposit = $this->getCreateCollectionDeposit($args);

        $this->assertEquals(TransactionDeposit::COLLECTION->value, $deposit);
    }

    public function test_deposit_for_create_collection_with_attributes()
    {
        $args = [
            'attributes' => [
                [
                    'key' => $key = fake()->word(),
                    'value' => $value = fake()->text(),
                ],
            ],
        ];

        $deposit = $this->getCreateCollectionDeposit($args);
        $totalBytes = count(Utils::string2ByteArray($key . $value));
        $totalDeposit = gmp_add(TransactionDeposit::ATTRIBUTE_BASE->toGMP(), gmp_mul(TransactionDeposit::ATTRIBUTE_PER_BYTE->toGMP(), $totalBytes));

        $this->assertEquals(gmp_strval(gmp_add(TransactionDeposit::COLLECTION->toGMP(), $totalDeposit)), $deposit);
    }

    public function test_deposit_for_set_attribute()
    {
        $args = [
            'key' => $key = fake()->word(),
            'value' => $value = fake()->text(),
        ];

        $deposit = $this->getSetAttributeDeposit($args);
        $totalBytes = count(Utils::string2ByteArray($key . $value));
        $totalDeposit = gmp_add(TransactionDeposit::ATTRIBUTE_BASE->toGMP(), gmp_mul(TransactionDeposit::ATTRIBUTE_PER_BYTE->toGMP(), $totalBytes));

        $this->assertEquals(gmp_strval($totalDeposit), $deposit);
    }

    public function test_deposit_for_create_token_with_empty_attributes()
    {
        $args = [
            'collectionId' => 2000,
            'params' => [
                'initialSupply' => 1,
                'attributes' => [],
            ],
        ];

        $deposit = $this->getCreateTokenDeposit($args);

        $this->assertEquals(TransactionDeposit::TOKEN_ACCOUNT->value, $deposit);
    }

    public function test_deposit_for_create_token_with_attributes()
    {
        $args = [
            'params' => [
                'initialSupply' => 1,
                'attributes' => [
                    [
                        'key' => 'string',
                        'value' => 'test',
                    ],
                ],
            ],
        ];

        $deposit = $this->getCreateTokenDeposit($args);

        $this->assertEquals('211000000000000000', $deposit);
    }

    public function test_deposit_for_mint_token()
    {
        $token = Token::factory()->create([
            'supply' => '1',
            'unit_price' => TransactionDeposit::TOKEN_ACCOUNT->value,
        ]);
        $collection = Collection::find($token->collection_id);

        $args = [
            'collectionId' => $collection->collection_chain_id,
            'params' => [
                'tokenId' => [
                    'integer' => $token->token_chain_id,
                ],
                'amount' => 1,
                'unitPrice' => null,
            ],
        ];

        $deposit = $this->getMintTokenDeposit($args);

        $this->assertEquals(TransactionDeposit::TOKEN_ACCOUNT->value, $deposit);
    }

    public function test_deposit_for_batch_create_token_with_empty_attributes()
    {
        $args = [
            'recipients' => [
                [
                    'createParams' => [
                        'initialSupply' => 1,
                        'attributes' => [],
                    ],
                ],
            ],
        ];

        $deposit = $this->getBatchMintDeposit($args);

        $this->assertEquals(TransactionDeposit::TOKEN_ACCOUNT->value, $deposit);
    }

    public function test_deposit_for_batch_create_token_with_attributes()
    {
        $args = [
            'recipients' => [
                [
                    'createParams' => [
                        'initialSupply' => 1,
                        'attributes' => [
                            [
                                'key' => 'string',
                                'value' => 'test',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $deposit = $this->getBatchMintDeposit($args);

        $this->assertEquals('211000000000000000', $deposit);
    }

    public function test_deposit_for_batch_set_attribute()
    {
        $args = [
            'attributes' => [
                [
                    'key' => 'uri',
                    'value' => 'localhost',
                ],
                [
                    'key' => 'name',
                    'value' => 'test',
                ],
            ],
        ];

        $deposit = $this->getBatchSetAttributeDeposit($args);

        $this->assertEquals('202000000000000000', $deposit);
    }
}
