<?php

namespace Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Extrinsics\FuelTanks;

use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Extrinsics\Extrinsic;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartExtrinsic;

class InsertRuleSet extends Extrinsic implements PolkadartExtrinsic {}

/*
[▼
  "hash" => "537044ac137b165605b6077d6df16f6db562a332c3f28fed1817bf3880bb4ad1"
  "extrinsic_length" => 164
  "version" => 4
  "signature" => array:3 [▼
    "address" => array:1 [▼
      "Id" => array:32 [▶]
    ]
    "signature" => array:1 [▼
      "Sr25519" => array:64 [▶]
    ]
    "signedExtensions" => array:4 [▼
      "era" => array:2 [▼
        "period" => 32
        "phase" => 22
      ]
      "nonce" => 134
      "tip" => "0"
      "metadata_hash" => "Disabled"
    ]
  ]
  "calls" => array:1 [▼
    "FuelTanks" => array:1 [▼
      "insert_rule_set" => array:3 [▼
        "tank_id" => array:1 [▼
          "Id" => array:32 [▼
            0 => 140
            1 => 184
            2 => 230
            3 => 192
            4 => 80
            5 => 13
            6 => 8
            7 => 132
            8 => 49
            9 => 34
            10 => 135
            11 => 124
            12 => 42
            13 => 192
            14 => 250
            15 => 84
            16 => 54
            17 => 112
            18 => 201
            19 => 96
            20 => 152
            21 => 168
            22 => 6
            23 => 104
            24 => 223
            25 => 99
            26 => 109
            27 => 254
            28 => 59
            29 => 148
            30 => 159
            31 => 19
          ]
        ]
        "rule_set_id" => 1
        "rule_set" => array:2 [▼
          "rules" => array:1 [▼
            0 => array:1 [▼
              "WhitelistedCollections" => array:1 [▼
                0 => "2000"
              ]
            ]
          ]
          "require_account" => false
        ]
      ]
    ]
  ]
  "extrinsic_hash" => "537044ac137b165605b6077d6df16f6db562a332c3f28fed1817bf3880bb4ad1"
]
*/
