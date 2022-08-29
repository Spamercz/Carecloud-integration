<?php

declare(strict_types = 1);

namespace App\Model\BigCommerce;

final class Order
{

    // TODO create all 60 parameters
    public function __construct(
        public readonly int $orderId,
        public readonly string $cartId,
        public readonly array $currency, // TODO DTO instead of array
        public readonly float $baseAmount,
        public readonly float $discountAmount,
    ) {}

}
