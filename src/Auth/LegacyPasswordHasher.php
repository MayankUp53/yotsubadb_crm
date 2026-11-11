<?php
declare(strict_types=1);

namespace App\Auth;

use Authentication\PasswordHasher\PasswordHasherInterface;

/**
 * Matches the original app's NonePasswordHasher: passwords remain unchanged.
 */
class LegacyPasswordHasher implements PasswordHasherInterface
{
    /**
     * Returns the password unchanged, matching the original app.
     *
     * @param string $password Plain text password to hash.
     * @return string Password hash.
     */
    public function hash(string $password): string
    {
        return $password;
    }

    /**
     * Compares the supplied and stored passwords directly.
     *
     * @param string $password Plain text password to hash.
     * @param string $hashedPassword Existing hashed password.
     * @return bool True if password matches, false otherwise.
     */
    public function check(string $password, string $hashedPassword): bool
    {
        return $password === $hashedPassword;
    }

    /**
     * Plain passwords are never automatically rehashed in the legacy flow.
     *
     * @param string $password The password to verify.
     * @return bool True if the password needs to be rehashed.
     */
    public function needsRehash(string $password): bool
    {
        return false;
    }
}

