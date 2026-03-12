<?php

namespace Infrastructure\JWT;

interface TokenGeneratorInterface
{
    public function sign(array $payload): string;
}
