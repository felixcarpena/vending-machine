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

    public static function zero(): self
    {
        return new self(0);
    }

    #[\NoDiscard]
    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    #[\NoDiscard]
    public function subtract(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function isLessThan(self $other): bool
    {
        return $this->cents < $other->cents;
    }

    public function isMultipleOf(self $other): bool
    {
        return $this->cents % $other->cents === 0;
    }
}
