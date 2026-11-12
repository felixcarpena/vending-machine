<?php

declare(strict_types=1);

namespace Vending\Domain;

final readonly class PayableChange
{
    /** @param array<int, true> $amounts */
    private function __construct(private array $amounts) {}

    public static function of(Money ...$amounts): self
    {
        $payable = [];
        foreach ($amounts as $amount) {
            $payable[$amount->cents] = true;
        }

        return new self($payable);
    }

    public function includes(Money $amount): bool
    {
        return isset($this->amounts[$amount->cents]);
    }
}
