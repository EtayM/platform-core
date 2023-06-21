<?php

return [
    'account_already_taken' => 'アカウントはすでに使用されています。',
    'auth.auth_not_defined' => '認証が定義されていません。',
    'auth.basic_token.token_not_defined' => '基本トークンが.envに定義されていません',
    'auth.driver_not_supported' => 'ドライバー [:driver] はサポートされていません。',
    'cannot_represent_object' => '次の値をオブジェクトとして表現できません：',
    'cannot_represent_uint256' => '次の値をuint256として表現できません：:value',
    'cannot_set_create_and_mint_params_with_same_recipient' => '同じ受取人にcreateパラメーターとmintパラメーターを設定できません。',
    'cannot_set_simple_and_operator_params_for_same_recipient' => '同じ受取人にsimpleパラメーターとoperatorパラメーターを設定できません。',
    'invalid_json' => '無効なJSON形式です。',
    'middleware.single_arg_only' => 'フィルターするフィールドを1つ選択してください。',
    'middleware.single_filter_only.only_one_filter' => 'これらのフィルターの1つのみが使用されます：:filterOptions',
    'middleware.single_filter_only.only_used_alone' => 'フィルター":filterOptions"は単独でしか使用できません。他のフィルターと組み合わせることはできません。',
    'not_valid_object' => '有効なオブジェクトではありません。',
    'not_valid_uint256' => '有効なuint256ではありません。',
    'serialization.method_does_not_exist' => "メソッド':method'は存在しません。",
    'set_either_create_or_mint_param_for_recipient' => '受取人ごとに、createパラメーターかmintパラメーターを設定する必要があります。',
    'set_either_simple_and_operator_params_for_recipient' => '受取人ごとに、simpleパラメーターかoperatorパラメーターを設定する必要があります。',
    'supply_cap_must_be_greater_than_initial' => '供給時価総額の金額は、初期供給以上である必要があります。',
    'supply_cap_must_be_set' => '供給時価総額を使用する場合、供給時価総額の金額が設定されている必要があります。',
    'there_can_only_one_input_name' => ':nameという入力フィールドは1つしか存在できません。',
    'token_id_encoder.encoder_config_not_defined' => 'エンコーダーの構成が定義されていません。',
    'token_id_encoder.encoder_not_supported' => 'エンコーダー [:driverClass] はサポートされていません。',
    'token_id_encoder.token_id_encoder_not_defined_in_env' => 'token_id_encoderが.envに定義されていません',
    'token_id_encoder.hash.algo_not_defined_in_config' => 'ハッシュアルゴリズムは構成に定義されていません。',
    'token_id_encoder.hash.algo_not_supported' => ':algoアルゴリズムはサポートされていません。',
    'token_id_encoder.string_id.requires_key_named_string' => 'StringIdエンコーダーには、stringという名前のキーを持つデータペイロードが必要です。',
    'token_int_too_large' => 'このtokenIdは、128bitユニットより大きく変換されるint値として使用できません。',
    'token_not_found' => 'トークンが見つかりません。',
    'transaction_not_found' => 'トランザクションが見つかりません。',
    'unable_to_load_metadata' => 'メタデータファイルを読み込めません。',
    'unauthorized_header' => '未承認です。有効な承認ヘッダーを提供してください。',
    'verification.invalid_signature' => '提供された署名は有効ではありません。',
    'verification.unable_to_generate_verification_id' => '確認IDを生成できません。',
    'verification.verification_not_found' => '確認が見つかりません。',
    'wallet_is_immutable' => 'ウォレットアカウントは一度設定されると変更できません。',
];
