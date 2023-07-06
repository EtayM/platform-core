<?php

namespace Enjin\Platform\Enums\Global;

use Enjin\Platform\Interfaces\PlatformCacheable;
use Enjin\Platform\Traits\EnumExtensions;
use Illuminate\Support\Collection;

enum PlatformCache: string implements PlatformCacheable
{
    use EnumExtensions;

    case METADATA = 'metadata';
    case CALL_INDEXES = 'callIndexes';
    case SYSTEM_ACCOUNT = 'systemAccount';
    case CUSTOM_TYPES = 'customTypes';
    case MANAGED_ACCOUNTS = 'managedAccounts';
    case BALANCE = 'balance';
    case BLOCK_EVENTS = 'blockEvents';
    case BLOCK_EXTRINSICS = 'blockExtrinsics';
    case SYNCING_IN_PROGRESS = 'syncingInProgress';
    case VERIFY_ACCOUNT = 'verifyAccount';
    case PAGINATION = 'pagination';

    public function key(?string $suffix = null): string
    {
        return 'enjin-platform:core:' . $this->value . ($suffix ? ":{$suffix}" : '');
    }

    public static function clearable(): Collection
    {
        return collect([
            self::METADATA,
            self::CALL_INDEXES,
            self::CUSTOM_TYPES,
            self::MANAGED_ACCOUNTS,
        ]);
    }
}
