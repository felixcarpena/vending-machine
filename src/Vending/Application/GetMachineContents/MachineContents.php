<?php

declare(strict_types=1);

namespace Vending\Application\GetMachineContents;

use Vending\Domain\Coins;

final readonly class MachineContents
{
    /** @param list<SlotContents> $slots */
    public function __construct(
        public array $slots,
        public Coins $coins,
    ) {}
}
