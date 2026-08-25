<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserMatchUsername implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    protected mixed $userId;

    public function __construct(mixed $userId)
    {
        $this->userId = $userId;
    }
    
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = User::where('id', $this->userId)
            ->where('username', $value)
            ->exists();

        if (!$exists) {
            $fail('شناسه کاربر و نام کاربری با یکدیگر مطابقت ندارند.');
        }
    }
}
