<?php

return [
    'accept_collection_transfer.args.collectionId' => 'The collection that will be accepted.',
    'accept_collection_transfer.description' => 'Accept a collection transfer request.',
    'acknowledge_events.args.uuids' => 'The event UUIDs to acknowledge.',
    'acknowledge_events.description' => 'Use this mutation to acknowledge cached events and remove them from the cache.',
    'add_to_tracked.args.chain_ids' => 'The on-chain model IDs to track, e.g. the collection IDs.',
    'add_to_tracked.args.hot_sync' => 'Hot syncing will import the chain data right away.  Turn this off if you intend to do a full sync instead.',
    'add_to_tracked.args.model_type' => 'The model type, e.g. COLLECTION',
    'add_to_tracked.description' => 'Add on-chain data to track.  Use this to limit which collections and tokens are synced and tracked on the platform.  If existing data exists on chain it will be imported.',
    'approve_collection.args.collectionId' => 'The collection that will be approved.',
    'approve_collection.args.operator' => 'The account that will be approved to operate the collection.',
    'approve_collection.description' => 'Approve another account to transfer any tokens from a collection account. You can also specify a block number where this approval will expire.',
    'approve_token.args.amount' => 'The amount of tokens it will be approved to operate.',
    'approve_token.args.collectionId' => 'The collection that the token that will be approved belongs to.',
    'approve_token.args.currentAmount' => 'The current amount of tokens the operator has.',
    'approve_token.args.expiration' => 'The block number where the approval will expire. Leave it as null for no expiration.',
    'approve_token.args.operator' => 'The account that will be approved to operate the token.',
    'approve_token.args.tokenId' => 'The token ID that will be approved.',
    'approve_token.description' => 'Approve another account to make transfers from a token account. You can also specify a block number where this approval will expire and the amount of tokens this account will be able to transfer.',
    'args.signingAccount' => 'The signing wallet for this transaction. Defaults to wallet daemon.',
    'args.skipValidation' => 'Skip all validation rules, use with caution. Defaults to false.',
    'batch_mint.args.collectionId' => 'The collection ID that you be minting the tokens to.',
    'batch_mint.description' => 'Use this method to batch together several mints into one transaction. You can mix and match Create Token and Mint Token params, as well as use the continueOnFailure flag to skip mints which fail on chain so they can be fixed later.',
    'batch_set_attribute.args.amount' => 'The amount to transfer.',
    'batch_set_attribute.args.collectionId' => 'The collection ID that you are adding attributes to.',
    'batch_set_attribute.args.continueOnFailure' => 'Whether to make the possible extrinsics if one of them fails. Defaults to false.',
    'batch_set_attribute.args.keepAlive' => 'If true, the transaction will fail if the balance drops below the minimum requirement. Defaults to False.',
    'batch_set_attribute.args.key' => 'The attribute key.',
    'batch_set_attribute.args.recipient' => 'The recipient account who is going to receive the transfer.',
    'batch_set_attribute.args.value' => 'The attribute value.',
    'batch_set_attribute.description' => 'Use this to set multiple attributes on a collection or token in one transaction. Setting the continueOnFailure flag to true will allow all valid attributes to be set while skipping invalid attributes so they can be fixed and attempted again in another transaction.',
    'batch_transfer.args.collectionId' => 'The collection ID that you be transferring the tokens from.',
    'batch_transfer.args.continueOnFailure' => 'Whether to make the possible extrinsics if one of them fails. Defaults to false.',
    'batch_transfer.args.signingAccount' => 'The signing wallet for this transaction. Defaults to wallet daemon.',
    'batch_transfer.description' => 'Use this method to transfer multiple tokens in one transaction. You can include up to 250 different transfers per batch. Set the continueOnFailure to true to allow all valid transfers to complete while skipping transfers which would fail so they can be fixed and attempted again in another transaction.',
    'batch_transfer_balance.description' => 'Transfer multiple balances in a batch. You can pass the keepAlive argument if you want to check if the account will be left with at least the existential deposit.',
    'burn.args.params' => 'The params required to burn a token.',
    'burn.description' => 'Deletes a collection and get its reserved value back. You can only destroy a collection after all tokens have been burned.',
    'common.args.collectionId' => 'The collection ID to use in this operation.',
    'common.args.continueOnFailure' => 'If set to true this option will skip data that would cause the whole batch to fail. Defaults to false.',
    'common.args.recipients' => 'The list of recipients that will receive tokens.',
    'create_collection.args.attributes' => 'Set initial attributes for this collection.',
    'create_collection.args.explicitRoyaltyCurrencies' => 'Set the explicit royalty currencies for tokens in this collection.',
    'create_collection.args.marketPolicy' => 'The marketplace policy for a collection.',
    'create_collection.args.mintPolicy' => 'Set the mint policy for tokens in this collection.',
    'create_collection.description' => 'Creates a new on-chain collection. The new collection ID will be returned in the transaction events after being finalized on-chain.',
    'create_token.args.recipient' => 'The recipient account of the tokens for the initial mint.',
    'create_token.description' => 'Creates a new token in a collection. The new token will be automatically transferred to the specified recipient account.',
    'create_wallet.args.externalId' => 'The external ID set for this wallet.',
    'create_wallet.description' => 'Store a new unverified wallet record using an external ID.',
    'freeze.args.collectionAccount' => 'The collection account to freeze.',
    'freeze.args.collectionId' => 'The collection ID to freeze.',
    'freeze.args.freezeState' => 'The freeze state type.',
    'freeze.args.freezeType' => 'The type of freezing to do.',
    'freeze.args.tokenAccount' => 'The token account to freeze.',
    'freeze.args.tokenId' => 'The token ID to freeze.',
    'freeze.description' => 'Freezes a collection, token, collection account or token account. Tokens cannot be transferred or burned if they are frozen. Freezing a collection or collection account will freeze all the tokens in it.',
    'link_wallet.description' => 'Note: This workflow and mutation are placeholder, please use the VerifyAccount flow to associate a wallet account to this platform.',
    'mark_and_list_pending_transactions.args.accounts' => 'The accounts to filter the transactions.',
    'mark_and_list_pending_transactions.args.mark_as_processing' => 'Sets whether the transactions should be moved into the processing state (default to true).',
    'mark_and_list_pending_transactions.args.network' => 'Optionally set the network to use.',
    'mark_and_list_pending_transactions.description' => 'Get a list of new pending transactions and mark them as processing.',
    'mint_token.args.collectionId' => 'The collection ID to mint from.',
    'mint_token.args.recipient' => 'The recipient account of the tokens being minted.',
    'mint_token.description' => 'Mint more of an existing token. This only applies to tokens which have a supply cap greater than 1.',
    'mutate_collection.args.collectionId' => 'The collection that will be mutated.',
    'mutate_collection.args.mutation' => 'The params that will be mutated.',
    'mutate_collection.args.tokenId' => 'The token that will be mutated.',
    'mutate_collection.description' => 'Changes collection default values.',
    'mutate_token.description' => 'Changes token default values.',
    'operator_transfer_token.args.params' => 'The operator transfer params.',
    'operator_transfer_token.description' => "Transfer tokens as the operator of someone else's wallet. Operator transfers are transfers that you make using tokens from somebody else's wallet as the source. To make this type of transfer the source wallet owner must approve you for transferring their tokens.",
    'remove_all_attributes.args.attributeCount' => 'This is an advanced feature and is used to calculate the weight of the on-chain extrinsic. Putting a value in that isn\'t equal to the on-chain attribute count will lead to the transaction failing. When empty, the attribute count will be auto calculated from data stored in the local database.',
    'remove_all_attributes.args.collectionId' => 'The collection ID to remove attributes from.',
    'remove_all_attributes.description' => 'Removes all attributes from the given collection ID and token ID.',
    'remove_collection.description' => 'Remove an attribute from the specified collection.',
    'remove_from_tracked.description' => 'Stop a collection from being tracked.',
    'remove_token_attribute.description' => 'Remove an attribute from the specified token.',
    'removed_from_tracked.description' => 'Remove on-chain data from being tracked.  Use this to remove collections and tokens from being track on the platform.  Already synced data will remain on the platform until synced again, but will not receive further updates from on-chain events.',
    'retry_transaction.description' => "Retries transactions that have failed or otherwise not been included on-chain after some time.  Use with caution and ensure the transactions really aren't yet on-chain (or likely to be) to make sure they are not accidentally included twice.",
    'send_transaction.args.signature' => 'The signature supplied from the user.',
    'send_transaction.args.signing_payload_json' => 'The signing payload to send in json format.',
    'set_collection_attribute.description' => 'Set an attribute on a collection.',
    'set_token_attribute.description' => 'Set an attribute on a token.',
    'set_wallet_account.description' => 'Set the account on a wallet model.',
    'simple_transfer_token.args.collectionId' => 'The collection ID you are transferring tokens from.',
    'simple_transfer_token.args.params' => 'The simple transfer params.',
    'simple_transfer_token.description' => 'Transfers a single token to a recipient account.',
    'thaw.args.collectionId' => 'The collection ID to thaw.',
    'thaw.args.freezeType' => 'The type of thawing to do.',
    'thaw.args.tokenAccount' => 'The token account to thaw.',
    'thaw.args.tokenId' => 'The token ID to thaw.',
    'thaw.description' => 'Thaw a previously frozen collection or token.',
    'transfer_all_balance.description' => 'Transfers all balances of an account to another. You can pass a keepAlive argument if you want to keep at least the existential deposit.',
    'transfer_balance.description' => 'Transfers a balance from one account to another. You can pass the keepAlive argument if you want to check if the account will be left with at least the existential deposit.',
    'unapprove_collection.args.collectionId' => 'The collection that approval will be removed from.',
    'unapprove_collection.args.operator' => 'The account that collection approval will be removed from.',
    'unapprove_collection.description' => 'Removes the approval of any specific account to make transfers from a collection account.',
    'unapprove_token.args.collectionId' => 'The collection that the token belongs to.',
    'unapprove_token.args.operator' => 'The account that token approval will be removed from.',
    'unapprove_token.args.tokenId' => 'The token that approval will be removed from.',
    'unapprove_token.description' => 'Removes the approval of any specific account to make transfers from a token account.',
    'update_external_id.description' => 'Change the external ID on a wallet model.',
    'update_transaction.args.signedAtBlock' => 'The block number the transaction was signed at.',
    'update_transaction.args.state' => 'The new state of the transaction.',
    'update_transaction.args.transactionHash' => 'The on chain transaction hash.',
    'update_transaction.args.transactionId' => 'The on chain transaction id.',
    'update_transaction.description' => 'Update a transaction with a new state, transaction ID and transaction hash. Please note that the transaction ID and transaction hash are immutable once set.',
    'update_transaction.error.hash_and_id_are_immutable' => 'The transaction id and hash are immutable once set.',
    'update_wallet_external_id.cannot_update_id_on_managed_wallet' => 'Cannot update the external id on a managed wallet.',
    'verify_account.description' => 'The wallet calls this mutation to prove the ownership of the user account.',
];
