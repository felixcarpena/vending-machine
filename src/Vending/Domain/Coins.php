<?php

declare(strict_types=1);

namespace Vending\Domain;

final readonly class Coins
{
    /** @var list<Coin> */
    private array $coins;

    private function __construct(Coin ...$coins)
    {
        $this->coins = array_values($coins);
    }

    public static function of(Coin ...$coins): self
    {
        return new self(...$coins);
    }

    public function countOf(Coin $coin): int
    {
        return count(array_filter($this->coins, static fn(Coin $held): bool => $held === $coin));
    }
}
