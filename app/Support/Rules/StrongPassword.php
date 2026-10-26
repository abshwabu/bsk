<?php

namespace App\Support\Rules;

use Illuminate\Validation\Rules\Password;

class StrongPassword
{
    /**
     * Get the standard strong password rule for Bina administrators.
     */
    public static function rule(): Password
    {
        return Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();
    }
}
