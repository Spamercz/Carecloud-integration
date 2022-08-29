<?php

declare(strict_types = 1);

namespace App\Model\BigCommerce;

use DateTime;

final class BigCommerceUser
{

    public function __construct(
        public readonly int $id,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly DateTime $dateOfBirth,
        public readonly bool $emailVerified,
        public readonly DateTime $signUpDate,
        public array $customFields = [],
    ) {}

}
