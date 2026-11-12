<?php

declare(strict_types=1);

namespace Vending\UI\Cli\Output;

use Vending\Application\GetMachineContents\MachineContents;
use Vending\Application\GetMachineContents\SlotContents;
use Vending\Domain\Coin;

final class StatusView
{
    /** @return list<string> */
    public static function of(MachineContents $contents): array
    {
        $slots = array_map(
            static fn(SlotContents $slot): string => sprintf(
                '%s holds %s at %s (%d left)',
                $slot->selector,
                $slot->product,
                MoneyFormat::of($slot->price),
                $slot->stock,
            ),
            $contents->slots,
        );
        $coins = array_map(
            static fn(Coin $coin): string => sprintf(
                '%s x%d',
                CoinFormat::of($coin),
                $contents->coins->countOf($coin),
            ),
            Coin::cases(),
        );

        return [...$slots, sprintf('Coins: %s', implode(', ', $coins))];
    }
}
