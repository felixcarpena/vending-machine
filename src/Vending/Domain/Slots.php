<?php

declare(strict_types=1);

namespace Vending\Domain;

use Vending\Domain\Exception\UnknownSlot;

final readonly class Slots
{
    /** @param array<string, Slot> $slots */
    private function __construct(private array $slots) {}

    public static function of(Slot ...$slots): self
    {
        $bySelector = [];
        foreach ($slots as $slot) {
            $bySelector[$slot->selector->value] = $slot;
        }

        return new self($bySelector);
    }

    public function find(Selector $selector): Slot
    {
        return $this->slots[$selector->value] ?? throw UnknownSlot::of($selector);
    }

    #[\NoDiscard]
    public function withOneUnitLessOf(Selector $selector): self
    {
        return new self([...$this->slots, $selector->value => $this->find($selector)->withOneUnitLess()]);
    }

    #[\NoDiscard]
    public function restockedTo(StockLevels $stock): self
    {
        foreach ($stock->selectors() as $selector) {
            if (!array_key_exists($selector->value, $this->slots)) {
                throw UnknownSlot::of($selector);
            }
        }

        return new self(array_map(
            static fn(Slot $slot): Slot => $slot->withStock($stock->unitsOf($slot->selector)),
            $this->slots,
        ));
    }

    /** @return list<Slot> */
    public function all(): array
    {
        return array_values($this->slots);
    }
}
