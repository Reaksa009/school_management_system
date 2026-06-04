<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class ExistsModel implements ValidationRule
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function __construct(private readonly string $modelClass)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! $this->modelClass::query()->whereKey($value)->exists()) {
            $fail(__('validation.exists'));
        }
    }
}
