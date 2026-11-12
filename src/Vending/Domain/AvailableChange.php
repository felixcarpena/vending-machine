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

    public function changeFor(Money $amount, ChangeMaker $changeMaker): ?Coins
    {
        return $changeMaker->changeFor($amount, $this->coins);
    }

    public function payableUpTo(Money $limit, ChangeMaker $changeMaker): PayableChange
    {
        return $changeMaker->payableUpTo($limit, $this->coins);
    }

    #[\NoDiscard]
    public function withdraw(Coins $coins): self
    {
        return new self($this->coins->minus($coins));
    }

    #[\NoDiscard]
    public function deposit(Coins $coins): self
    {
        return new self($this->coins->plus($coins));
    }
}
