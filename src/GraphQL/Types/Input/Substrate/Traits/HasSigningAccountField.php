<?php

namespace Enjin\Platform\GraphQL\Types\Input\Substrate\Traits;

use Enjin\Platform\Rules\ValidSubstrateAccount;
use Enjin\Platform\Support\Account;
use Facades\Enjin\Platform\Services\Database\WalletService;
use Illuminate\Support\Arr;
use Rebing\GraphQL\Support\Facades\GraphQL;

trait HasSigningAccountField
{
    /**
     * Get the signing account field.
     */
    public function getSigningAccountField(
        ?string $signingAccountDesc = null,
    ): array {
        $signingAccountType = [
            'type' => GraphQL::type('String'),
            'description' => $signingAccountDesc ?: __('enjin-platform::args.signingAccount'),
            'rules' => ['nullable', new ValidSubstrateAccount()],
        ];

        return [
            'signingAccount' => $signingAccountType,
        ];
    }

    /**
     * Get the signing account.
     */
    public function getSigningAccount(array $args)
    {
        return WalletService::firstOrStore([
            'account' => empty($signing = Arr::get($args, 'signingAccount')) ? Account::daemonPublicKey() : $signing,
        ]);
    }
}
