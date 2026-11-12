<?php

declare(strict_types=1);

namespace Vending\Domain;

final readonly class OutputTray
{
    private function __construct(public Coins $coins) {}

    public static function empty(): self
    {
        return new self(Coins::of());
    }

    #[\NoDiscard]
    public function withCoins(Coins $coins): self
    {
        return new self($this->coins->plus($coins));
    }

    public function isEmpty(): bool
    {
        return $this->coins->isEmpty();
    }
}
