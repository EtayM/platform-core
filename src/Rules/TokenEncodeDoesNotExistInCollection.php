<?php

namespace Enjin\Platform\Rules;

use Closure;
use Enjin\Platform\Rules\Traits\HasDataAwareRule;
use Enjin\Platform\Services\Database\TokenService;
use Enjin\Platform\Services\Token\TokenIdManager;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TokenEncodeDoesNotExistInCollection implements DataAwareRule, ValidationRule
{
    use HasDataAwareRule;

    /**
     * The token service.
     */
    protected TokenService $tokenService;

    /**
     * The token id manager service.
     */
    protected TokenIdManager $tokenIdManager;

    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        $this->tokenService = resolve(TokenService::class);
        $this->tokenIdManager = resolve(TokenIdManager::class);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $data = Arr::get($this->data, Str::beforeLast($attribute, '.'));

        if ($this->tokenService->tokenExistsInCollection(
            $this->tokenIdManager->encode($data),
            $this->data['collectionId']
        )) {
            $fail('enjin-platform::validation.token_encode_doesnt_exist_in_collection')->translate();
        }
    }
}
