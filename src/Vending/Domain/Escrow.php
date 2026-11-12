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

    /** @return iterable<Coins> */
    public function paymentsFor(Money $price): iterable
    {
        $countsByTotal = [0 => []];
        foreach ($this->coins->largestFirst() as $coin) {
            foreach ($countsByTotal as $total => $counts) {
                $withThisCoin = $total + $coin->value;
                if (!array_key_exists($withThisCoin, $countsByTotal)) {
                    $counts[$coin->value] = ($counts[$coin->value] ?? 0) + 1;
                    $countsByTotal[$withThisCoin] = $counts;
                }
            }
        }
        ksort($countsByTotal);

        foreach ($countsByTotal as $total => $counts) {
            if ($total >= $price->cents) {
                yield self::coinsFrom($counts);
            }
        }
    }

    /** @param array<int, int> $counts */
    private static function coinsFrom(array $counts): Coins
    {
        $coins = [];
        foreach ($counts as $cents => $count) {
            $coins = [...$coins, ...array_fill(0, $count, Coin::from($cents))];
        }

        return Coins::of(...$coins);
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
