<?php

namespace Infrastructure\Hash;

use Illuminate\Support\Facades\Hash;

final class BcryptHasher implements PasswordHasherInterface
{
    public function hash(string $value): string
    {
        return Hash::make($value, ['rounds' => 12]);
    }

    public function verify(string $value, string $hashedValue): bool
    {
        return Hash::check($value, $hashedValue);
    }
}
