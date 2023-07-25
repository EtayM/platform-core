<?php

// config for Platform/Core

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | This defines what authentication method to use to protect the APIs.
    | If set to empty|null, the endpoints will not be protected.
    | It's strongly recommended to set one.
    |
    */
    'auth' => env('AUTH_DRIVER'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Methods
    |--------------------------------------------------------------------------
    |
    | These are the supported authentication drivers
    |
    */
    'auth_drivers' => [
        'basic_token' => [
            'driver' => 'basic_token',
            'token' => env('BASIC_AUTH_TOKEN'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Uses decoder container
    |--------------------------------------------------------------------------
    |
    | If you wish to use a decoder container set the host here.
    |
    */
    'decoder_container' => env('DECODER_CONTAINER', '127.0.0.1:8090'),

    /*
    |--------------------------------------------------------------------------
    | Deep links
    |--------------------------------------------------------------------------
    |
    | Here you can change the deep links used throughout the platform.
    |
    */
    'deep_links' => [
        'proof' => rtrim(env('PROOF_DEEPLINK', env('APP_URL', 'http://localhost')), '/') . '/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Token ID Encoder
    |--------------------------------------------------------------------------
    |
    | This defines the default encoder to use to encode your token ID
    |
    */
    'token_id_encoder' => env('TOKEN_ID_ENCODER', 'hash'),

    /*
    |--------------------------------------------------------------------------
    | Token ID Encoders
    |--------------------------------------------------------------------------
    |
    | These are the different encoders supported base from the best practices
    | https://platform.docs.enjin.io/getting-started-with-the-platform-api/tokenid-structure-best-practices
    |
    */
    'token_id_encoders' => [
        'hash' => [
            'driver' => 'hash',
            'algo' => 'blake2',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The blockchain networks
    |--------------------------------------------------------------------------
    |
    | These are the list of networks that platform is currently supporting.
    | You may configure the network setting for each network.
    |
    */
    'chains' => [
        'supported' => [
            'substrate' => [
                'enjin' => [
                    'chain-id' => 0,
                    'network-id' => 2000,
                    'testnet' => false,
                    'platform-id' => env('SUBSTRATE_ENJIN_PLATFORM_ID', 0),
                    'node' => env('SUBSTRATE_ENJIN_RPC', 'wss://rpc.matrix.blockchain.enjin.io'),
                    'ss58-prefix' => env('SUBSTRATE_ENJIN_SS58_PREFIX', 12120),
                ],
                'canary' => [
                    'chain-id' => 0,
                    'network-id' => 2010,
                    'testnet' => true,
                    'platform-id' => env('SUBSTRATE_CANARY_PLATFORM_ID', 0),
                    'node' => env('SUBSTRATE_CANARY_RPC', 'wss://rpc.matrix.canary.enjin.io'),
                    'ss58-prefix' => env('SUBSTRATE_CANARY_SS58_PREFIX', 9030),
                ],
                'polkadot' => [
                    'chain-id' => 0,
                    'network-id' => 101,
                    'testnet' => false,
                    'platform-id' => env('EFINITY_POLKADOT_PLATFORM_ID', 0),
                    'node' => env('EFINITY_POLKADOT_RPC', 'wss://rpc.efinity.io:443'),
                    'ss58-prefix' => env('EFINITY_POLKADOT_SS58_PREFIX', 1110),
                ],
                'local' => [
                    'chain-id' => 0,
                    'network-id' => 104,
                    'testnet' => true,
                    'platform-id' => env('EFINITY_LOCAL_PLATFORM_ID', 0),
                    'node' => env('EFINITY_LOCAL_RPC', 'ws://localhost:10010'),
                    'ss58-prefix' => env('EFINITY_LOCAL_SS58_PREFIX', 195),
                ],
            ],
        ],

        'selected' => env('CHAIN', 'substrate'),

        'network' => env('NETWORK', 'polkadot'),

        'daemon-account' => env('DAEMON_ACCOUNT') ?: '0x0000000000000000000000000000000000000000000000000000000000000000',
    ],

    /*
    |--------------------------------------------------------------------------
    | The pagination limit
    |--------------------------------------------------------------------------
    |
    | Here you may set the default pagination limit for the APIs
    |
    */
    'pagination' => [
        'limit' => env('DEFAULT_PAGINATION_LIMIT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | The indexing IDs
    |--------------------------------------------------------------------------
    |
    | Here you may set the collection chain IDs for the indexer.
    |
    */
    'indexing' => [
        'filters' => [
            'collections' => array_filter(explode(',', env('INDEX_COLLECTIONS', ''))),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | The flag to cache event
    |--------------------------------------------------------------------------
    |
    | When true, events are cached
    |
    */
    'cache_events' => env('PLATFORM_CACHE_EVENTS', true),

    /*
    |--------------------------------------------------------------------------
    | The websocket channel name
    |--------------------------------------------------------------------------
    |
    | Here you may configure the name of the websocket channel
    |
    */
    'platform_channel' => env('PLATFORM_CHANNEL', 'platform'),


    /*
    |--------------------------------------------------------------------------
    | The QR Image URL Adapter
    |--------------------------------------------------------------------------
    |
    | Set the adapter for generating the QR URL.
    |
    */
    'qr' => [
        'adapter' => \Enjin\Platform\Services\Qr\Adapters\GoogleQrAdapter::class,
        'size' => env('BEAM_QR_SIZE', 512),
    ],

    /*
    |--------------------------------------------------------------------------
    | The ingest sync wait timeout
    |--------------------------------------------------------------------------
    |
    | Here you may set how long the ingest command to wait for the sync to finish
    |
    */
    'sync_max_wait_timeout' => env('SYNC_MAX_WAIT_TIMEOUT', 3600),

];
