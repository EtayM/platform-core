<?php

namespace Enjin\Platform\Tests\Unit;

use Codec\Base;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use Enjin\Platform\Enums\Substrate\PalletIdentifier;
use Enjin\Platform\Enums\Substrate\TokenMintCapType;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Support\Blake2;
use Enjin\Platform\Support\Twox;
use Enjin\Platform\Tests\TestCase;

final class StorageTest extends TestCase
{
    protected Codec $codec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codec = new Codec();
    }

    public function test_it_can_encode_twoxs()
    {
        $twox128 = Twox::hash('MultiTokens');

        $this->assertEquals(
            'fa7484c926e764ee2a64df96876c8145',
            $twox128
        );
    }

    public function test_it_can_encode_blake2()
    {
        $publicKey = '4d1bf8eb687839f94c706719717b4ad2ddf001eebd650c24fe94f5ce21f6acd6';
        $blake = Blake2::hash($publicKey, 128);

        $this->assertEquals(
            '7834ca8ec759d72404963785f497bd89',
            $blake
        );
    }

    public function test_it_can_call_right_slot()
    {
        $publicKey = '4d1bf8eb687839f94c706719717b4ad2ddf001eebd650c24fe94f5ce21f6acd6';

        $systemHashed = Twox::hash('System');
        $accountHashed = Twox::hash('Account');
        $keyHashed = Blake2::hash($publicKey, 128);

        $slot = $systemHashed . $accountHashed . $keyHashed . $publicKey;

        $this->assertEquals(
            '26aa394eea5630e07c48ae0c9558cef7b99d880ec681799c0cf30e8886371da97834ca8ec759d72404963785f497bd894d1bf8eb687839f94c706719717b4ad2ddf001eebd650c24fe94f5ce21f6acd6',
            $slot
        );
    }

    public function test_it_can_call_multi_tokens_balances_account()
    {
        $publicKey = 'd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d';
        $multiTokensHashed = Twox::hash('MultiTokens');
        $balancesHashed = Twox::hash('Balances');
        $keyHashed = Blake2::hash($publicKey, 128);

        $slot = $multiTokensHashed . $balancesHashed . $keyHashed . $publicKey;

        $this->assertEquals('fa7484c926e764ee2a64df96876c8145c2261276cc9d1f8598ea4b6a74b15c2fde1e86a9a8c739864cf3cc5ec2bea59fd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d', $slot);
    }

    public function test_it_can_decode_attribute_storage_key()
    {
        $content = $this->codec->decode()->attributeStorageKey('0xfa7484c926e764ee2a64df96876c8145761e97790c81676703ce25cc0ffeb3773ba80a3778f04ebf45e806d19a0520250100000000000000000000000000000007f95f6b3baacab308323526a6eedc2201adde00000000000000000000000000006eb1501c909e2b877fbd045ffc11bc26106e616d65');

        $this->assertEquals(
            [
                'collectionId' => '1',
                'tokenId' => '57005',
                'attribute' => '6e616d65',
            ],
            $content
        );
    }

    public function test_it_can_decode_u128()
    {
        $codec = new ScaleInstance(Base::create());
        $this->assertEquals(0, gmp_cmp(gmp_init('57005'), $codec->process('U128', new ScaleBytes('0xadde0000000000000000000000000000'))));
    }

    public function test_it_can_decode_collection_storage_key()
    {
        $content = $this->codec->decode()->collectionStorageKey('0xfa7484c926e764ee2a64df96876c81459200647b8c99af7b8b52752114831bdba68417e9769fad205e3d67e4cef9d822dc050000000000000000000000000000');
        $this->assertEquals(
            [
                'collectionId' => '1500',
            ],
            $content
        );
    }

    public function test_it_can_decode_collection_storage_data()
    {
        $content = $this->codec->decode()->collectionStorageData('0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d00000000018eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48025a62020c0017000010a59e86fdde43040000');

        $this->assertEquals(
            [
                'owner' => '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
                'maxTokenCount' => null,
                'maxTokenSupply' => null,
                'forceSingleMint' => false,
                'burn' => null,
                'isFrozen' => false,
                'attribute' => null,
                'royaltyBeneficiary' => '0x8eaf04151687736326c9fea17e25fc5287613693c912909cb226aa4794f26a48',
                'royaltyPercentage' => 1,
                'tokenCount' => '3',
                'attributeCount' => '0',
                'totalDeposit' => '1252000000000000000000',
                'explicitRoyaltyCurrencies' => [
                    [
                        'collectionId' => '0',
                        'tokenId' => '0',
                    ],
                ],
            ],
            $content
        );
    }

    public function test_it_can_decode_token_storage_key()
    {
        $content = $this->codec->decode()->tokenStorageKey('0xfa7484c926e764ee2a64df96876c814599971b5749ac43e0235e41b0d37869183ba80a3778f04ebf45e806d19a052025010000000000000000000000000000003d4d415ebb3ec1e0f570a4086ca65d5fff000000000000000000000000000000');
        $this->assertEquals(
            [
                'collectionId' => '1',
                'tokenId' => '255',
            ],
            $content
        );
    }

    public function test_it_can_decode_token_storage_data_with_single_mint()
    {
        $content = $this->codec->decode()->tokenStorageData('0x0401000004010f0000c16ff286230f0000c16ff2862304000000');
        $this->assertEquals(
            [
                'supply' => '1',
                'cap' => TokenMintCapType::SINGLE_MINT,
                'capSupply' => null,
                'isFrozen' => false,
                'minimumBalance' => '1',
                'unitPrice' => '10000000000000000',
                'mintDeposit' => '10000000000000000',
                'attributeCount' => '1',
                'royaltyBeneficiary' => null,
                'royaltyPercentage' => null,
                'isCurrency' => false,
                'listingForbidden' => false,
            ],
            $content
        );
    }

    public function test_it_can_decode_token_storage_data_with_supply()
    {
        $content = $this->codec->decode()->tokenStorageData('0x214e0101419c0004010f0000c16ff2862317000088b116afe3b50204000000');
        $this->assertEquals(
            [
                'supply' => '5000',
                'cap' => TokenMintCapType::SUPPLY,
                'capSupply' => '10000',
                'isFrozen' => false,
                'minimumBalance' => '1',
                'unitPrice' => '10000000000000000',
                'mintDeposit' => '50000000000000000000',
                'attributeCount' => '1',
                'royaltyBeneficiary' => null,
                'royaltyPercentage' => null,
                'isCurrency' => false,
                'listingForbidden' => false,
            ],
            $content
        );
    }

    public function test_it_can_decode_collection_accounts_storage_key()
    {
        $content = $this->codec->decode()->collectionAccountStorageKey('0x0bf891b100bbc75a3aaa261402ae0e8bc8511ac575318ec7f93a67d1cdf292da3ba80a3778f04ebf45e806d19a05202501000000000000000000000000000000de1e86a9a8c739864cf3cc5ec2bea59fd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d');
        $this->assertEquals(
            [
                'collectionId' => '1',
                'accountId' => '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
            ],
            $content
        );
    }

    public function test_it_can_decode_collection_accounts_storage_data()
    {
        $content = $this->codec->decode()->collectionAccountStorageData('0x0004d43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d0004');
        $this->assertEquals(
            [
                'isFrozen' => false,
                'approvals' => [
                    [
                        'accountId' => '0xd43593c715fdd31c61141abd04a99fd6822c8558854ccde39a5684e7a56da27d',
                        'expiration' => null,
                    ],
                ],
                'accountCount' => '1',
            ],
            $content
        );
    }

    public function test_it_can_decode_token_accounts_storage_key()
    {
        $content = $this->codec->decode()->tokenAccountStorageKey('0xfa7484c926e764ee2a64df96876c8145091ba7dd8dcd80d727d06b71fe08a1030137310a1fc3eee361a5ba3e0250053c3e0800000000000000000000000000000127b5fce16694cf2ce4e2ada82c2f1a050000000000000000000000000000006693450ba38c572dc228966702d125c12ea037a549132b4f4e8a372c7e288014ac324c8e97e1c647c8e4bac2bb9ddd18');
        $this->assertEquals(
            [
                'accountId' => '0x2ea037a549132b4f4e8a372c7e288014ac324c8e97e1c647c8e4bac2bb9ddd18',
                'collectionId' => '2110',
                'tokenId' => '5',
            ],
            $content
        );
    }

    public function test_it_can_decode_token_accounts_storage_data()
    {
        $content = $this->codec->decode()->tokenAccountStorageData('0x04000000000880f4bee67ab5c177239bfc89d9d307c65afaf10fb6d7d63487a9a2d9df8f460504009cc25de9d468a701b070397bc63b94a7aa5afb72c33cc2990ae004ce014ab333040150c3000000');
        $this->assertEquals(
            [
                'balance' => '1',
                'reservedBalance' => '0',
                'lockedBalance' => '0',
                'namedReserves' => [],
                'approvals' => [
                    [
                        'accountId' => '0x80f4bee67ab5c177239bfc89d9d307c65afaf10fb6d7d63487a9a2d9df8f4605',
                        'amount' => '1',
                        'expiration' => null,
                    ],
                    [
                        'accountId' => '0x9cc25de9d468a701b070397bc63b94a7aa5afb72c33cc2990ae004ce014ab333',
                        'amount' => '1',
                        'expiration' => '50000',
                    ],
                ],
                'isFrozen' => false,
            ],
            $content
        );
    }

    public function test_it_can_decode_token_accounts_storage_with_named_reserves()
    {
        $content = $this->codec->decode()->tokenAccountStorageData('0x140c00046d61726b74706c6303000000000000000000000000000000000000');
        $this->assertEquals(
            [
                'balance' => '5',
                'reservedBalance' => '3',
                'lockedBalance' => '0',
                'namedReserves' => [
                    [
                        'pallet' => PalletIdentifier::MARKETPLACE,
                        'amount' => 3,
                    ],
                ],
                'approvals' => [],
                'isFrozen' => false,
            ],
            $content
        );
    }
}
