<?php

declare(strict_types=1);

namespace Vending\UI\Cli\Output;

use Vending\Application\GetMachineContents\MachineContents;
use Vending\Application\GetMachineContents\SlotContents;

final class StatusView
{
    /** @return list<string> */
    public static function of(MachineContents $contents): array
    {
        return array_map(
            static fn(SlotContents $slot): string => sprintf(
                '%s holds %s at %s (%d left)',
                $slot->selector,
                $slot->product,
                MoneyFormat::of($slot->price),
                $slot->stock,
            ),
            $contents->slots,
        );
    }
}
