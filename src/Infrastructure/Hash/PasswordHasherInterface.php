<?php

namespace Infrastructure\Hash;

interface PasswordHasherInterface
{
    public function hash(string $value): string;
    public function verify(string $value, string $hashedValue): bool;
}
