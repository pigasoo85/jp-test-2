<?php
// Make safety great again

function getCSRF(): string
{
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function verifyCSRF(string $token): bool
{
    return isset($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}

// XSS filter
function safetyInput(string $data): string
{
    return htmlspecialchars(
        trim($data),
        ENT_QUOTES,
        'UTF-8'
    );
}


function errorMessage(string $code): string
{
    static $map = [

        // ===== Register =====
        'passport.register.too_many_requests' =>
            'Too many registration attempts. Please try again later.',

        'passport.name.invalid_length' =>
            'Username must be between 3 and 20 characters long.',

        'passport.name.exists' =>
            'This username is already taken.',

        'passport.password.invalid_length' =>
            'Password must be at least 6 characters long.',


        // ===== Login =====
        'passport.account.locked' =>
            'Too many failed login attempts. Please try again later.',

        'passport.account.invalid' =>
            'Invalid username or password.',


        // ===== System =====
        'system.error_500' =>
            'An internal server error occurred. Please try again later.',
    ];

    return $map[$code] ?? 'Unknown error.';
}
