<?php

namespace Enjin\Platform\Rules;

use Enjin\Platform\Services\Database\CollectionService;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class ApprovalExistsInCollection implements DataAwareRule, Rule
{
    /**
     * All of the data under validation.
     */
    protected array $data = [];

    /**
     * The collection service.
     */
    protected CollectionService $collectionService;

    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        $this->collectionService = app()->make(CollectionService::class);
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
        return $this->collectionService->approvalExistsInCollection($this->data['collectionId'], $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('enjin-platform::validation.approval_exists_in_collection', ['operator' => $this->data['operator'], 'collectionId' => $this->data['collectionId']]);
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
