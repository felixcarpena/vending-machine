<?php

declare(strict_types=1);

namespace Vending\Infrastructure;

use Vending\Domain\AvailableChange;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
use Vending\Domain\Slot;
use Vending\Domain\Slots;
use Vending\Domain\StartingSetup;

final class StartingSetupFromConfiguration
{
    /**
     * @param array<string, array{product: string, price: int, stock: int}> $slots
     * @param array<int, int> $change
     */
    public static function read(array $slots, array $change): StartingSetup
    {
        $startingSlots = [];
        foreach ($slots as $selector => $slot) {
            $startingSlots[] = new Slot(
                Selector::of($selector),
                Product::named($slot['product']),
                Money::fromCents($slot['price']),
                $slot['stock'],
            );
        }

        $startingChange = [];
        foreach ($change as $cents => $count) {
            $startingChange = [...$startingChange, ...array_fill(0, $count, Coin::from($cents))];
        }

        return StartingSetup::of(Slots::of(...$startingSlots), AvailableChange::of(Coins::of(...$startingChange)));
    }
}
