<?php

declare(strict_types=1);

namespace Vending\Domain;

final readonly class AvailableChange
{
    private function __construct(public Coins $coins) {}

    public static function of(Coins $coins): self
    {
        return new self($coins);
    }

    #[\NoDiscard]
    public function deposit(Coins $coins): self
    {
        return new self($this->coins->plus($coins));
    }
}
