<?php

namespace Enjin\Platform\Services\Processor\Substrate\Codec;

use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Enums\Substrate\PalletIdentifier;
use Enjin\Platform\Enums\Substrate\TokenMintCapType;
use Enjin\Platform\Models\Substrate\CreateTokenParams;
use Enjin\Platform\Models\Substrate\FreezeTypeParams;
use Enjin\Platform\Models\Substrate\MintParams;
use Enjin\Platform\Models\Substrate\MintPolicyParams;
use Enjin\Platform\Models\Substrate\RoyaltyPolicyParams;
use Illuminate\Support\Arr;

class Decoder
{
    protected ScaleInstance $codec;

    public function __construct(ScaleInstance $codec)
    {
        $this->codec = $codec;
    }

    public function compact(string $data)
    {
        return $this->codec->process('Compact<u128>', new ScaleBytes($data));
    }

    public function systemAccount(?string $data = null): array
    {
        $decoded = $data === null ? null : $this->codec->process('AccountInfoWithTripleRefCount', new ScaleBytes($data));

        return [
            'nonce' => Arr::get($decoded, 'nonce', 0),
            'consumers' => Arr::get($decoded, 'consumers', 0),
            'providers' => Arr::get($decoded, 'providers', 0),
            'sufficients' => Arr::get($decoded, 'sufficients', 0),
            'balances' => [
                'free' => gmp_strval(Arr::get($decoded, 'data.free', '0')),
                'reserved' => gmp_strval(Arr::get($decoded, 'data.reserved', '0')),
                'miscFrozen' => gmp_strval(Arr::get($decoded, 'data.miscFrozen', '0')),
                'feeFrozen' => gmp_strval(Arr::get($decoded, 'data.feeFrozen', '0')),
            ],
        ];
    }

    public function createCollection(string $data): array
    {
        $decoded = $this->codec->process('CreateCollection', new ScaleBytes($data));

        return [
            'mintPolicy' => MintPolicyParams::fromEncodable(Arr::get($decoded, 'descriptor.policy.mint'))->toArray(),
            'marketPolicy' => ($royalty = Arr::get($decoded, 'descriptor.policy.market')) !== null
                ? RoyaltyPolicyParams::fromEncodable($royalty)->toArray()
                : null,
        ];
    }

    public function destroyCollection(string $data): array
    {
        $decoded = $this->codec->process('DestroyCollection', new ScaleBytes($data));

        return [
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
        ];
    }

    public function mint(string $data): array
    {
        $decoded = $this->codec->process('Mint', new ScaleBytes($data));
        $params = Arr::get($decoded, 'params');

        return [
            'recipientId' => ($recipient = Arr::get($decoded, 'recipient.Id')) !== null ? HexConverter::prefix($recipient) : null,
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
            'params' => Arr::exists($params, 'CreateToken') ?
                CreateTokenParams::fromEncodable(Arr::get($params, 'CreateToken'))->toArray()
                :
                MintParams::fromEncodable(Arr::get($params, 'Mint'))->toArray(),
        ];
    }

    public function burn(string $data): array
    {
        $decoded = $this->codec->process('Burn', new ScaleBytes($data));

        return [
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
            'tokenId' => gmp_strval(Arr::get($decoded, 'params.tokenId')),
            'amount' => gmp_strval(Arr::get($decoded, 'params.amount')),
            'keepAlive' => Arr::get($decoded, 'params.keepAlive'),
            'removeTokenStorage' => Arr::get($decoded, 'params.removeTokenStorage'),
        ];
    }

    public function freeze(string $data): array
    {
        $decoded = $this->codec->process('Freeze', new ScaleBytes($data));

        return [
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
            'freezeType' => FreezeTypeParams::fromEncodable(Arr::get($decoded, 'freezeType'))->toArray(),
        ];
    }

    public function thaw(string $data): array
    {
        $decoded = $this->codec->process('Thaw', new ScaleBytes($data));

        return [
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
            'freezeType' => FreezeTypeParams::fromEncodable(Arr::get($decoded, 'freezeType'))->toArray(),
        ];
    }

    public function setAttribute(string $data): array
    {
        $decoded = $this->codec->process('SetAttribute', new ScaleBytes($data));

        return [
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
            'tokenId' => ($value = Arr::get($decoded, 'tokenId')) !== null ? gmp_strval($value) : null,
            'key' => HexConverter::hexToString(Arr::get($decoded, 'key')),
            'value' => HexConverter::hexToString(Arr::get($decoded, 'value')),
        ];
    }

    public function removeAttribute(string $data): array
    {
        $decoded = $this->codec->process('RemoveAttribute', new ScaleBytes($data));

        return [
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
            'tokenId' => ($value = Arr::get($decoded, 'tokenId')) !== null ? gmp_strval($value) : null,
            'key' => HexConverter::hexToString(Arr::get($decoded, 'key')),
        ];
    }

    public function bytes(string $data)
    {
        return $this->codec->process('Bytes', new ScaleBytes($data));
    }

    public function attributeStorageKey(string $data): array
    {
        $decoded = $this->codec->process('AttributeStorage', new ScaleBytes($data));

        return [
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
            'tokenId' => gmp_strval(Arr::get($decoded, 'tokenId')),
            'attribute' => Arr::get($decoded, 'attribute'),
        ];
    }

    public function collectionStorageKey(string $data): array
    {
        $decoded = $this->codec->process('CollectionStorageKey', new ScaleBytes($data));

        return [
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
        ];
    }

    public function collectionStorageData(string $data): array
    {
        $decoded = $this->codec->process('CollectionStorageData', new ScaleBytes($data));

        return [
            'owner' => ($owner = Arr::get($decoded, 'owner')) !== null ? HexConverter::prefix($owner) : null,
            'maxTokenCount' => ($value = Arr::get($decoded, 'policy.mint.maxTokenCount')) !== null ? gmp_strval($value) : null,
            'maxTokenSupply' => ($value = Arr::get($decoded, 'policy.mint.maxTokenSupply')) !== null ? gmp_strval($value) : null,
            'forceSingleMint' => Arr::get($decoded, 'policy.mint.forceSingleMint'),
            'burn' => Arr::get($decoded, 'policy.burn'),
            'isFrozen' => Arr::get($decoded, 'policy.transfer.isFrozen'),
            'royaltyBeneficiary' => ($beneficiary = Arr::get($decoded, 'policy.market.royalty.beneficiary')) !== null ? HexConverter::prefix($beneficiary) : null,
            'royaltyPercentage' => ($percentage = Arr::get($decoded, 'policy.market.royalty.percentage')) !== null ? $percentage / 10 ** 7 : null,
            'attribute' => Arr::get($decoded, 'policy.attribute'),
            'tokenCount' => gmp_strval(Arr::get($decoded, 'tokenCount')),
            'attributeCount' => gmp_strval(Arr::get($decoded, 'attributeCount')),
            'totalDeposit' => gmp_strval(Arr::get($decoded, 'totalDeposit')),
            'explicitRoyaltyCurrencies' => Arr::get($decoded, 'explicitRoyaltyCurrencies'),
        ];
    }

    public function tokenStorageKey(string $data): array
    {
        $decoded = $this->codec->process('TokenStorageKey', new ScaleBytes($data));

        return [
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
            'tokenId' => gmp_strval(Arr::get($decoded, 'tokenId')),
        ];
    }

    public function tokenStorageData(string $data): array
    {
        $decoded = $this->codec->process('CanaryTokenStorageData', new ScaleBytes($data));
        $cap = TokenMintCapType::tryFrom(collect(Arr::get($decoded, 'cap'))->keys()->first()) ?? TokenMintCapType::INFINITE;
        $isCurrency = Arr::exists(Arr::get($decoded, 'marketBehavior') ?: [], 'IsCurrency');
        $isFrozen = in_array(Arr::get($decoded, 'freezeState'), ['Permanent', 'Temporary']);
        $unitPrice = Arr::get($decoded, 'sufficiency.Insufficient');

        return [
            'supply' => gmp_strval(Arr::get($decoded, 'supply')),
            'cap' => $cap,
            'capSupply' => ($supply = Arr::get($decoded, 'cap.Supply')) !== null ? gmp_strval($supply) : null,
            'isFrozen' => $isFrozen,
            'royaltyBeneficiary' => ($beneficiary = Arr::get($decoded, 'marketBehavior.HasRoyalty.beneficiary')) !== null ? HexConverter::prefix($beneficiary) : null,
            'royaltyPercentage' => ($percentage = Arr::get($decoded, 'marketBehavior.HasRoyalty.percentage')) !== null ? $percentage / 10 ** 7 : null,
            'isCurrency' => $isCurrency,
            'listingForbidden' => Arr::get($decoded, 'listingForbidden'),
            'minimumBalance' => gmp_strval(Arr::get($decoded, 'minimumBalance')),
            'unitPrice' => gmp_strval($unitPrice),
            'mintDeposit' => gmp_strval(Arr::get($decoded, 'mintDeposit')),
            'attributeCount' => gmp_strval(Arr::get($decoded, 'attributeCount')),
        ];
    }

    public function collectionAccountStorageKey(string $data): array
    {
        $decoded = $this->codec->process('CollectionAccountsStorageKey', new ScaleBytes($data));

        return [
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
            'accountId' => HexConverter::prefix(Arr::get($decoded, 'accountId')),
        ];
    }

    public function collectionAccountStorageData(string $data): array
    {
        $decoded = $this->codec->process('CollectionAccountsStorageData', new ScaleBytes($data));

        $approvals = collect(Arr::get($decoded, 'approvals'))->map(
            fn ($expiration, $account) => [
                'accountId' => HexConverter::prefix($account),
                'expiration' => $expiration !== null ? gmp_strval($expiration) : null,
            ]
        )->values()->toArray();

        return [
            'isFrozen' => Arr::get($decoded, 'isFrozen'),
            'approvals' => $approvals,
            'accountCount' => gmp_strval(Arr::get($decoded, 'accountCount')),
        ];
    }

    public function tokenAccountStorageKey(string $data): array
    {
        $decoded = $this->codec->process('CanaryTokenAccountsStorageKey', new ScaleBytes($data));

        return [
            'accountId' => HexConverter::prefix(Arr::get($decoded, 'accountId')),
            'collectionId' => gmp_strval(Arr::get($decoded, 'collectionId')),
            'tokenId' => gmp_strval(Arr::get($decoded, 'tokenId')),
        ];
    }

    public function tokenAccountStorageData(string $data): array
    {
        $decoded = $this->codec->process('TokenAccountsStorageData', new ScaleBytes($data));

        $approvals = collect(Arr::get($decoded, 'approvals'))->map(
            fn ($approval, $account) => [
                'accountId' => HexConverter::prefix($account),
                'amount' => gmp_strval($approval['amount']),
                'expiration' => ($expiration = $approval['expiration']) !== null ? gmp_strval($expiration) : null,
            ]
        )->values()->toArray();

        $namedReserves = collect(Arr::get($decoded, 'namedReserves'))->map(
            fn ($reserve, $pallet) => [
                'pallet' => PalletIdentifier::fromHex($pallet),
                'amount' => gmp_strval($reserve),
            ]
        )->values()->toArray();

        return [
            'balance' => gmp_strval(Arr::get($decoded, 'balance')),
            'reservedBalance' => gmp_strval(Arr::get($decoded, 'reservedBalance')),
            'lockedBalance' => gmp_strval(Arr::get($decoded, 'lockedBalance')),
            'namedReserves' => $namedReserves,
            'approvals' => $approvals,
            'isFrozen' => Arr::get($decoded, 'isFrozen'),
        ];
    }
}
