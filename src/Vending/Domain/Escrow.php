<?php

declare(strict_types=1);

namespace Vending\Domain;

final readonly class Escrow
{
    private function __construct(public Coins $coins) {}

    public static function empty(): self
    {
        return new self(Coins::of());
    }

    #[\NoDiscard]
    public function insert(Coin $coin): self
    {
        return new self($this->coins->add($coin));
    }

    public function isEmpty(): bool
    {
        return $this->coins->isEmpty();
    }

    public function total(): Money
    {
        return $this->coins->total();
    }
}
