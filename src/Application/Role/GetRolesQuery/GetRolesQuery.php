<?php

namespace Application\Role\GetRolesQuery;

final readonly class GetRolesQuery
{
    /** Query class for fetching roles */
    public function __construct(
        public ?string $search = null,
        public ?bool $isActive = null
    ) {
    }
}
