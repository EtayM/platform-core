<?php

namespace Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Extrinsics\Marketplace;

use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartExtrinsic;
use Illuminate\Support\Arr;

class FinalizeAuction implements PolkadartExtrinsic
{
    public readonly string $signer;
    public readonly string $hash;
    public readonly int $index;
    public readonly string $module;
    public readonly string $call;
    public readonly array $params;

    public static function fromChain(array $data): self
    {
        $self = new self();
        $self->signer = Arr::get($data, 'signature.address.Id');
        $self->hash = Arr::get($data, 'extrinsic_hash');
        $self->module = array_key_first(Arr::get($data, 'call'));
        $self->call = array_key_first(Arr::get($data, 'call.' . $self->module));
        $self->params = Arr::get($data, 'call.' . $self->module . '.' . $self->call);

        return $self;
    }
}

/*
{
    "extrinsic_length": 138,
    "version": 4,
    "signature": {
        "address": {
            "Id": "e4569fb538b1cb511472919417e748d96aaab546f15d89f3d387122ab72eef79"
        },
        "signature": {
            "Sr25519": "a02d7a581fb0adf14b31de4016d0da0b9ccc205f49635eefc56dcc3da7617708bace909fd14177c45c79e41333a0592dfa870c83c604d9e98851b062b4bb1b85"
        },
        "signedExtensions": {
            "CheckMortality": {
                "Mortal116": 0
            },
            "CheckNonce": 79,
            "ChargeTransactionPayment": "0"
        }
    },
    "call": {
        "Marketplace": {
            "finalize_auction": {
                "listing_id": "0aabea26ce1a43e14775b4b2be60e08a1bc3fcbaaeedfba2f3a5ce934d24d73e"
            }
        }
    },
    "extrinsic_hash": "0x0be7f6575781b22339ee08f1812a5db6f2ebf9685378c4cee36004de4297ac69"
}
*/
