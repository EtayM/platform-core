<?php

return [
    'account_already_taken' => 'The account has already been taken.',
    'auth.auth_not_defined' => 'The auth is not defined.',
    'auth.basic_token.token_not_defined' => 'The basic token is not defined in your .env',
    'auth.driver_not_supported' => 'Driver [:driver] is not supported.',
    'auth.null_driver_not_allowed_in_production' => 'The Null auth driver cannot be used in production.',
    'cannot_represent_integer_range' => 'Cannot represent following value as integer range: :value',
    'cannot_represent_integer_ranges_array' => 'Cannot represent following value as integer ranges array: :value',
    'cannot_represent_object' => 'Cannot represent following value as object: ',
    'cannot_represent_uint256' => 'Cannot represent following value as uint256: :value',
    'cannot_set_create_and_mint_params_with_same_recipient' => 'Cannot set create params and mint params for the same recipient.',
    'cannot_set_simple_and_operator_params_for_same_recipient' => 'Cannot set simple params and operator params for the same recipient.',
    'invalid_json' => 'Invalid json format.',
    'middleware.single_arg_only' => 'Please supply just one field.',
    'middleware.single_filter_only.only_one_filter' => 'Only one of these filter(s) can be used: :filterOptions',
    'middleware.single_filter_only.only_used_alone' => 'The filter(s) ":filterOptions" can only be used alone. You cannot combine them with other filters.',
    'not_valid_integer_range' => 'Not a valid integer range.',
    'not_valid_integer_ranges_array' => 'Not a valid integer ranges array.',
    'not_valid_object' => 'Not a valid object.',
    'not_valid_uint256' => 'Not a valid uint256.',
    'qr.data_must_not_be_empty' => 'The QR data payload must not be empty.',
    'qr.extension_not_installed' => 'The Imagick PHP extension is required to generate png QR codes.',
    'qr.image_format_not_supported' => 'The requested image format is not supported.',
    'serialization.method_does_not_exist' => "Method ':method' does not exist.",
    'signing_payload_json_is_invalid' => 'The signing payload json is invalid.',
    'set_either_create_or_mint_param_for_recipient' => 'You need to set either create params or mint params for every recipient.',
    'set_either_simple_and_operator_params_for_recipient' => 'You need to set either simple params or operator params for every recipient.',
    'supply_cap_must_be_greater_than_initial' => 'Supply CAP amount must be greater than or equal to initial supply.',
    'supply_cap_must_be_set' => 'Supply CAP amount must be set when using Supply CAP.',
    'there_can_only_one_input_name' => 'There can be only one input field named ":name".',
    'token_id_encoder.encoder_config_not_defined' => 'The encoder config is not defined.',
    'token_id_encoder.encoder_not_supported' => 'Encoder [:driverClass] is not supported.',
    'token_id_encoder.invalid_data' => 'Invalid data supplied to encoder.',
    'token_id_encoder.token_id_encoder_not_defined_in_env' => 'The token_id_encoder is not defined in the .env',
    'token_id_encoder.hash.algo_not_defined_in_config' => 'The hash algo is not defined in the config.',
    'token_id_encoder.hash.algo_not_supported' => 'Algo :algo is not supported.',
    'token_int_too_large' => 'This tokenId cannot be used as the int value it converts to is larger than a 128bit uint.',
    'token_not_found' => 'Token not found.',
    'transaction_not_found' => 'Transaction not found.',
    'unable_to_load_metadata' => 'Unable to load the metadata files.',
    'unauthorized_header' => 'Unauthorized. Please provide a valid Authorization header.',
    'verification.invalid_signature' => 'The signature provided is not valid.',
    'verification.unable_to_generate_verification_id' => 'Unable to generate a verification id.',
    'verification.verification_not_found' => 'Verification not found.',
    'wallet_is_immutable' => 'The wallet account is immutable once set.',
    'skip_validation_field_not_found' => 'When using HasSkippableRules trait, you must provide a skipValidation field.',
    'attribute_count_empty' => 'The attribute count for this collection or token is empty.',
    'failed_to_truncate' => 'Failed to truncate the tables...',
    'exception_in_sync' => 'We got an exception in the sync process:',
    'failed_to_get_current_block' => 'Failed to get the current block...',
    'line_and_file' => 'Line :line in :file',
    'unable_to_process' => "Sorry, we're unable to process your request at this time. Please try again later.",
    'no_collection' => 'Unable to find a collection for token ID :tokenId.',
    'cannot_retry_transaction' => 'Cannot retry FINALIZED transaction.',
];
