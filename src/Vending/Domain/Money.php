<?php

declare(strict_types=1);

namespace Vending\Domain;

use Vending\Domain\Exception\NegativeMoney;

final readonly class Money
{
    private function __construct(public int $cents)
    {
        if ($cents < 0) {
            throw NegativeMoney::of($cents);
        }
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }
}
