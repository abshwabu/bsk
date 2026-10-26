<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Log;

class Login extends BaseLogin
{
    /**
     * Log failed login attempts for audit and security monitoring.
     */
    protected function throwFailureValidationException(): never
    {
        Log::warning('Failed Filament admin login attempt', [
            'ip' => request()->ip(),
            'email' => $this->data['email'] ?? null,
            'user_agent' => request()->userAgent(),
        ]);

        parent::throwFailureValidationException();
    }
}
