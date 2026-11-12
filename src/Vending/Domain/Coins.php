<?php

declare(strict_types=1);

namespace Vending\Domain;

use LogicException;

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

    #[\NoDiscard]
    public function add(Coin $coin): self
    {
        return new self(...[...$this->coins, $coin]);
    }

    #[\NoDiscard]
    public function plus(self $other): self
    {
        return new self(...$this->coins, ...$other->coins);
    }

    #[\NoDiscard]
    public function minus(self $other): self
    {
        $remaining = $this->coins;
        foreach ($other->coins as $coin) {
            $position = array_search($coin, $remaining, true);
            if ($position === false) {
                throw new LogicException(sprintf('There is no %d cents coin to take.', $coin->value));
            }
            unset($remaining[$position]);
        }

        return new self(...$remaining);
    }

    public function countOf(Coin $coin): int
    {
        return count(array_filter($this->coins, static fn(Coin $held): bool => $held === $coin));
    }

    public function isEmpty(): bool
    {
        return $this->coins === [];
    }

    public function total(): Money
    {
        return array_reduce(
            $this->coins,
            static fn(Money $total, Coin $coin): Money => $total->add($coin->value()),
            Money::zero(),
        );
    }

    /** @return list<Coin> */
    public function largestFirst(): array
    {
        $coins = $this->coins;
        usort($coins, static fn(Coin $one, Coin $other): int => $other->value <=> $one->value);

        return $coins;
    }
}
