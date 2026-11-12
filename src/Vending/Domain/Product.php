<?php

declare(strict_types=1);

namespace Vending\Domain;

use Vending\Domain\Exception\InvalidProduct;

final readonly class Product
{
    private function __construct(public string $name)
    {
        if ($name === '') {
            throw InvalidProduct::becauseItHasNoName();
        }
    }

    public static function named(string $name): self
    {
        return new self(trim($name));
    }
}
