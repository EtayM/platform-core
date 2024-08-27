<?php

return [
    'account.description' => 'A substrate account.',
    'account.field.address' => 'The account address.',
    'account.field.publicKey' => 'The account public key.',
    'account_request.description' => 'A request to verify an account.',
    'account_request.field.proofCode' => 'The proof code for this request.',
    'account_request.field.proofUrl' => 'The proof URL to use instead of (or alongside) the QR Code.',
    'account_request.field.qrCode' => 'The QR code a user can scan in the wallet app to verify their account.',
    'account_request.field.verificationId' => 'This is a verification ID generated to get the account from.',
    'account_verified.description' => 'The verification status of an account.',
    'account_verified.field.account' => 'The account that was verified.',
    'account_verified.field.verified' => 'If the user account has already been verified.',
    'attribute.description' => 'An on-chain key/value pair.',
    'attribute_input.description' => 'The attribute for a collection or token.',
    'balances.description' => 'The balance properties for a wallet account.',
    'balances.feeFrozen' => 'The frozen fee balance of the account.',
    'balances.free' => 'The free balance of the account.',
    'balances.miscFrozen' => 'The frozen misc balance of the account.',
    'balances.reserved' => 'The reserved balance of the account.',
    'big_int.description' => 'A type that represents unsigned integers up to 256 bits. The value must be a PHP numeric (int or string) and must not use scientific notation.',
    'block.description' => 'A blockchain block.',
    'block.field.exception' => 'The exception that happened if a block failed to process.',
    'block.field.failed' => 'If the block failed to be processed.',
    'block.field.hash' => 'The on-chain block hash.',
    'block.field.id' => 'The internal ID of the block.',
    'block.field.number' => 'The on-chain block number.',
    'block.field.synced' => 'If the block was already synced.',
    'collection.description' => 'A collection groups together tokens and sets the policies that apply to them.',
    'collection_account.description' => "A collection account groups together a wallet's token accounts for a given collection and controls options such as freezing and approvals for all tokens in them.",
    'collection_account.field.accountCount' => 'The number of token accounts attached to this collection account.',
    'collection_account.field.approvals' => 'A list of approvals for this account.',
    'collection_account.field.collection' => 'The collection this collection account belongs to.',
    'collection_account.field.isFrozen' => 'Specifies if this collection account is frozen.',
    'collection_account.field.namedReserves' => 'The named reserves for this account.',
    'collection_account.field.wallet' => 'The wallet which owns this collection account.',
    'collection_account_approval.description' => 'The wallets that have been approved to use this collection account.',
    'collection_account_approval.field.account' => 'The collection account this approval belongs to.',
    'collection_account_approval.field.expiration' => 'The expiration block the wallet will lose the approval.',
    'collection_account_approval.field.wallet' => 'The wallet that has been approved.',
    'collection_type.field.accounts' => 'The accounts for this collection.',
    'collection_type.field.attributes' => 'The attributes for this collection.',
    'collection_type.field.collectionId' => 'The ID assigned to this collection.',
    'collection_type.field.forceSingleMint' => 'Whether the tokens in this collection will be minted as SingleMint types. This would indicate the tokens in this collection are NFTs.',
    'collection_type.field.frozen' => 'Whether this collection is frozen.',
    'collection_type.field.maxTokenCount' => 'The maximum number of tokens that can be issued for this collection.',
    'collection_type.field.maxTokenSupply' => 'The maximum amount of each token in this collection that can be minted.',
    'collection_type.field.network' => 'The network this collection belongs to.',
    'collection_type.field.owner' => 'The wallet which can mint tokens from this collection.',
    'collection_type.field.royalty' => 'Specifies if this token has a royalty policy.',
    'collection_type.field.tokens' => 'The tokens minted from this collection.',
    'description' => "The fundamental unit of any GraphQL Schema is the type. There are many kinds of types in GraphQL as represented by the `__TypeKind` enum.\nDepending on the kind of a type, certain fields describe information about that type. Scalar types provide no information beyond a name and description, while Enum types provide their values. Object and Interface types provide the fields they describe. Abstract types, Union and Interface, provide the Object types possible at runtime. List and NonNull types compose other types.",
    'edge.field.node' => 'List of items on the current cursor.',
    'event.description' => 'A blockchain event.',
    'event.field.eventId' => 'The event ID.',
    'event.field.lookUp' => 'The method look up.',
    'event.field.moduleId' => 'The pallet module.',
    'event.field.params' => 'The params from this event.',
    'event.field.phase' => 'The phase of block execution it happened.',
    'event_param.description' => 'An event param.',
    'event_param.field.type' => 'The value type of the param.',
    'event_param.field.value' => 'The value of the param.',
    'integer_range.description' => "A string value that can be used to represent a range of integer numbers.  Use a double full stop to supply a range between 2 integers.  For example an integer range that looks like this: \n\n\"3..8\"\n\nWill be automatically expanded to:\n\n[3, 4, 5, 6, 7, 8]",
    'integer_ranges_array.description' => "An array that can be used to represent ranges of integer numbers.  Use a double full stop to supply a range between 2 integers in the array.  For example an integer ranges array that looks like this: \n\n[\"1\", \"3..8\", \"11\", \"15..18\"]\n\nWill be automatically expanded to:\n\n[1, 3, 4, 5, 6, 7, 8, 11, 15, 16, 17, 18]",
    'json.description' => 'A type that represents json data.',
    'page_info.field.endCursor' => 'The next cursor.',
    'page_info.field.hasNextPage' => 'Determines if cursor has more pages after the current page.',
    'page_info.field.hasPreviousPage' => 'Determines if cursor has more pages before the current page.',
    'page_info.field.startCursor' => 'The previous cursor.',
    'pending_event.description' => 'A websocket event pending to be acknowledge.',
    'pending_event.field.channels' => 'The channels the event was sent to.',
    'pending_event.field.data' => 'The data of the event.',
    'pending_event.field.id' => 'The internal ID of the event.',
    'pending_event.field.name' => 'The name of the event.',
    'pending_event.field.sent' => 'The timestamp when the event was sent.',
    'pending_event.field.uuid' => 'The UUID of the event.',
    'pending_event.field.network' => 'The blockchain network.',
    'royalty.description' => 'Royalty settings.',
    'string_filter_input.description' => 'A string filter.',
    'string_filter_input.filter.description' => 'The term to filter to.',
    'string_filter_input.type.description' => 'The type of the filter: AND / OR.',
    'token.description' => 'A token on the blockchain. Tokens have settings specific to them and can also have their own attributes which can be used to override the parent collection attributes.',
    'token.field.accounts' => 'The token accounts that hold this token.',
    'token.field.attributeCount' => 'The number of attributes set on this token.',
    'token.field.attributes' => 'The token attributes.',
    'token.field.cap' => 'The maximum quantity available for this token.',
    'token.field.collection' => 'The collection this token belongs to.',
    'token.field.isCurrency' => 'Shows if this token is a currency. Being a currency makes the token fungible automatically.',
    'token.field.isFrozen' => 'Specifies if this token is frozen, disallowing transfers.',
    'token.field.minimumBalance' => 'The minimum required balance of this token for all accounts.',
    'token.field.mintDeposit' => 'The amount of currency reserved from the issuer for minting.',
    'token.field.name' => 'The name of the new token.',
    'token.field.nonFungible' => 'Shows if this token considered non-fungible (i.e. there is only one available and therefore truly unique).',
    'token.field.royalty' => 'Returns the token royalty if set, or null if not.',
    'token.field.supply' => 'The current supply of this token.',
    'token.field.tokenId' => 'The token chain ID which is a 128bit unsigned integer number.',
    'token.field.unitPrice' => 'The price of each token in ENJ.',
    'token_account.description' => "A token account stores a wallet's balance of a specific token in a collection.",
    'token_account.field.balance' => 'The balance of the token this account holds.',
    'token_account.field.collection' => 'The collection this token account belongs to.',
    'token_account.field.isFrozen' => 'Specifies if this token account is frozen, disallowing transfers.',
    'token_account.field.reservedBalance' => 'The reserved value for this account.',
    'token_account.field.token' => 'The token for this account.',
    'token_account.field.wallet' => 'The wallet which owns this token account.',
    'token_account_approval.args.amount' => 'The amount the wallet has been approved.',
    'token_account_approval.description' => 'The wallets that have been approved to use this token account.',
    'token_account_approval.field.account' => 'The token account this approval belongs to.',
    'token_account_named_reserve.args.amount' => 'The amount in the wallet that has been reserved.',
    'token_account_named_reserve.args.pallet' => 'The pallet that has created this reserve.',
    'token_account_named_reserve.description' => 'The pallet that has reserved some tokens and the amount.',
    'transaction.description' => 'An blockchain transaction.',
    'transaction.eth.description' => 'An Ethereum transaction.',
    'transaction.eth.field.transactionId' => 'The transaction hash.',
    'transaction.field.createdAt' => 'The date and time the transaction was created.',
    'transaction.field.deposit' => 'The deposit made for this transaction.',
    'transaction.field.encodedData' => 'The encoded transaction data.',
    'transaction.field.events' => 'The events generated by this transaction.',
    'transaction.field.fee' => 'The fee paid by this transaction.',
    'transaction.field.idempotencyKey' => 'The idempotency key set for this transaction.',
    'transaction.field.method' => 'The on-chain method used.',
    'transaction.field.network' => 'The network used for this transaction.',
    'transaction.field.result' => 'The transaction result.',
    'transaction.field.signedAtBlock' => 'The block number the transaction was signed at.',
    'transaction.field.signingPayload' => 'The signing payload to make an extrinsic.',
    'transaction.field.signingPayload.nonce' => 'The nonce of the account to make the extrinsic.',
    'transaction.field.signingPayload.tip' => 'The tip to make the extrinsic.',
    'transaction.field.state' => 'The transaction state.',
    'transaction.field.transactionHash' => 'The on-chain transaction hash.',
    'transaction.field.transactionId' => 'The on-chain transaction ID.',
    'transaction.field.updatedAt' => 'The date and time the transaction was last updated.',
    'transaction.field.wallet' => 'The wallet used for signing this transaction.',
    'wallet.address.' => 'The wallet address.',
    'wallet.description' => 'A blockchain wallet.',
    'wallet.field.account' => 'The wallet account.',
    'wallet.field.balances' => "The ENJ balance of the account. The balances will be null if the wallet doesn't exist on the blockchain.",
    'wallet.field.collectionAccountApprovals' => 'The collection account approvals this wallet has.',
    'wallet.field.collectionAccounts' => 'The collection accounts this wallet has.',
    'wallet.field.collectionIds' => 'The collection to return.',
    'wallet.field.externalId' => 'The external ID associated with the wallet.',
    'wallet.field.id' => 'The internal ID of the wallet.',
    'wallet.field.managed' => 'Whether this is a managed wallet.',
    'wallet.field.network' => 'The blockchain network this wallet belongs to.',
    'wallet.field.nonce' => "The nonce of the account. A nonce will be null if the wallet doesn't exist on the blockchain.",
    'wallet.field.ownedCollections' => 'The collections this wallet owns.',
    'wallet.field.tokenAccountApprovals' => 'The token account approvals this wallet has.',
    'wallet.field.tokenAccounts' => 'The token accounts this wallet owns. Token accounts store the balances of tokens.',
    'wallet.field.transactions' => 'The transactions performed by this wallet.',
    'wallet_link.field.code' => 'The code a user can input into the wallet app to link their account on the platform.',
];
