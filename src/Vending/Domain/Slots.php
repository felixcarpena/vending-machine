<?php

declare(strict_types=1);

namespace Vending\Domain;

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

    /** @return list<Slot> */
    public function all(): array
    {
        return array_values($this->slots);
    }
}
