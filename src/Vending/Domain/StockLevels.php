<?php

declare(strict_types=1);

namespace Vending\Domain;

use Vending\Domain\Exception\NegativeStock;

final readonly class StockLevels
{
    /** @param array<string, int> $units */
    private function __construct(private array $units) {}

    /** @param array<string, int> $unitsBySelector */
    public static function of(array $unitsBySelector): self
    {
        $units = [];
        foreach ($unitsBySelector as $code => $count) {
            $selector = Selector::of($code);
            if ($count < 0) {
                throw NegativeStock::of($selector, $count);
            }
            $units[$selector->value] = $count;
        }

        return new self($units);
    }

    public function unitsOf(Selector $selector): int
    {
        return $this->units[$selector->value] ?? 0;
    }

    /** @return list<Selector> */
    public function selectors(): array
    {
        return array_map(Selector::of(...), array_keys($this->units));
    }
}
