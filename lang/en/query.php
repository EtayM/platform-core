<?php

return [
    'get_account_verified.args.account' => 'The wallet account that you want to check if it is verified.',
    'get_account_verified.args.verificationId' => 'The verification ID that you want to check if it was verified.',
    'get_account_verified.description' => 'Get the verification status of an account.',
    'get_blocks.args.hashes' => 'The blockchain transaction hashes to filter to.',
    'get_blocks.args.number' => 'The blockchain transaction IDs to filter to.',
    'get_collection.args.collectionId' => 'The on-chain collection ID to get.',
    'get_collection.description' => 'Get a collection by its collection ID.',
    'get_collections.args.collectionIds' => 'The on-chain collection IDs to filter to.',
    'get_collections.description' => 'Get an array of collections optionally filtered by collection IDs.',
    'get_pending_events.args.acknowledgeEvents' => 'Automatically acknowledge all returned events (defaults to false).',
    'get_pending_events.description' => 'Get a list of events that were broadcast but not yet acknowledged.',
    'get_pending_wallets.description' => 'Get an array of wallet accounts which have yet to be verified.',
    'get_token.args.collectionId' => 'The token collection ID.',
    'get_token.args.tokenId' => 'The specific token ID to get.',
    'get_token.description' => 'Get a token from a collection using its token ID.',
    'get_tokens.args.collectionId' => 'The Collection to return tokens from.',
    'get_tokens.args.tokenIds' => 'Filter to specific token IDs or omit to return all.',
    'get_tokens.description' => 'Get an array of tokens from a collection, optionally filtered by token IDs.',
    'get_transaction.args.id' => 'The internal ID of the transaction.',
    'get_transaction.args.idempotencyKey' => 'The idempotency keys to filter to.',
    'get_transaction.args.transactionHash' => 'The blockchain transaction hash.',
    'get_transaction.args.transactionId' => 'The blockchain transaction id.',
    'get_transaction.description' => 'Get a transaction using its database ID, on-chain transaction ID or transaction hash.',
    'get_transactions.args.ids' => 'The internal ID of the transaction.',
    'get_transactions.args.idempotencyKeys' => 'The idempotency keys to filter to.',
    'get_transactions.args.transactionHashes' => 'The blockchain transaction hash.',
    'get_transactions.args.transactionIds' => 'The blockchain transaction id.',
    'get_transactions.args.signedAtBlocks' => 'The block numbers that the transactions were signed at.',
    'get_transactions.args.accounts' => 'The wallet accounts to filter to.',
    'get_transactions.args.methods' => 'The transaction method types to filter to.',
    'get_transactions.args.results' => 'Filter transactions to the specified results.',
    'get_transactions.args.states' => 'Filter transactions to the specified states.',
    'get_transactions.description' => 'Get an array of transactions optionally filtered by transaction IDs, transaction hashes, methods, states, results or accounts.',
    'get_wallet.args.account' => 'The wallet account on the blockchain.',
    'get_wallet.args.externalId' => 'The external ID for this wallet.',
    'get_wallet.args.id' => 'The internal ID of this wallet.',
    'get_wallet.args.newExternalId' => 'The new external ID to set for this wallet.',
    'get_wallet.args.verificationId' => 'The verification ID of this wallet.',
    'get_wallet.description' => 'Get a wallet using either its database ID, external ID, verification ID or account address.',
    'get_wallets.description' => 'Get wallets using either its database ID, external ID, verification ID or account address.',
    'request_account.args.callback' => 'This is the callback URL that the wallet should send the verification to.',
    'request_account.description' => 'This query generates a QR code that the user can scan to give us their wallet account.',
    'verify_message.args.cryptoSignatureType' => 'The signature crypto type. This field is optional and it will use sr25519 by default.',
    'verify_message.args.message' => 'The message that the user signed.',
    'verify_message.args.publicKey' => 'The public key of the user.',
    'verify_message.args.signature' => 'The signed message.',
    'verify_message.description' => 'Verifies a message was signed with the public key provided.',
];
