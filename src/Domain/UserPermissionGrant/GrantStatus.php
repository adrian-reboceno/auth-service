<?php

namespace Domain\UserPermissionGrant;

enum GrantStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
