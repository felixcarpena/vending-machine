<?php

declare(strict_types=1);

namespace Vending\Infrastructure;

use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
use Vending\Domain\Slot;
use Vending\Domain\Slots;
use Vending\Domain\StartingSetup;

final class StartingSetupFromConfiguration
{
    /** @param array<string, array{product: string, price: int, stock: int}> $slots */
    public static function read(array $slots): StartingSetup
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

        return StartingSetup::of(Slots::of(...$startingSlots));
    }
}
