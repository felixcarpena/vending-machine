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

    /** @return list<Coins> */
    public function paymentsFor(Money $price): array
    {
        $payments = [Coins::of()];
        foreach (Coin::cases() as $coin) {
            $withThisCoin = [];
            foreach ($payments as $payment) {
                for ($count = 0; $count <= $this->coins->countOf($coin); ++$count) {
                    $withThisCoin[] = $payment->plus(Coins::of(...array_fill(0, $count, $coin)));
                }
            }
            $payments = $withThisCoin;
        }

        $enough = array_filter($payments, static fn(Coins $payment): bool => !$payment->total()->isLessThan($price));
        usort($enough, static fn(Coins $one, Coins $other): int => $one->total()->cents <=> $other->total()->cents);

        return $enough;
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
