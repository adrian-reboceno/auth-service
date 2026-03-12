<?php

namespace Domain\Token;

enum TokenStatus: string
{
    case Active  = 'active';
    case Revoked = 'revoked';
    case Expired = 'expired';
    case Used    = 'used';
}
