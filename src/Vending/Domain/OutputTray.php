<?php

declare(strict_types=1);

namespace Vending\Domain;

final readonly class OutputTray
{
    /** @param list<Product> $products */
    private function __construct(
        public array $products,
        public Coins $coins,
    ) {}

    public static function empty(): self
    {
        return new self([], Coins::of());
    }

    #[\NoDiscard]
    public function withProduct(Product $product): self
    {
        return new self([...$this->products, $product], $this->coins);
    }

    #[\NoDiscard]
    public function withCoins(Coins $coins): self
    {
        return new self($this->products, $this->coins->plus($coins));
    }

    public function isEmpty(): bool
    {
        return $this->products === [] && $this->coins->isEmpty();
    }
}
