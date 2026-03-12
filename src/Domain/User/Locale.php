<?php

namespace Domain\User;

use Domain\User\Exceptions\InvalidLocaleException;

final class Locale
{
    private const ALLOWED = ['es', 'en'];

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function from(string $locale): self
    {
        if (!in_array($locale, self::ALLOWED, true)) {
            throw new InvalidLocaleException("Unsupported locale: {$locale}");
        }

        return new self($locale);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
