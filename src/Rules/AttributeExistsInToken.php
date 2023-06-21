<?php

namespace Enjin\Platform\Rules;

use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\HasEncodableTokenId;
use Enjin\Platform\Services\Database\TokenService;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class AttributeExistsInToken implements DataAwareRule, Rule
{
    use HasEncodableTokenId;

    /**
     * All of the data under validation.
     */
    protected $data = [];

    /**
     * The token service.
     */
    protected TokenService $tokenService;

    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        $this->tokenService = app()->make(TokenService::class);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$tokenId = $this->encodeTokenId($this->data)) {
            return true;
        }

        return $this->tokenService->attributeExistsInToken($this->data['collectionId'], $tokenId, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('enjin-platform::validation.key_doesnt_exit_in_token');
    }

    /**
     * Set the data under validation.
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
