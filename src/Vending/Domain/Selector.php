<?php

declare(strict_types=1);

namespace Vending\Domain;

use Vending\Domain\Exception\InvalidSelector;

final readonly class Selector
{
    private function __construct(public string $value)
    {
        if ($value === '') {
            throw InvalidSelector::becauseItIsEmpty();
        }
    }

    public static function of(string $value): self
    {
        return new self(strtoupper(trim($value)));
    }
}
