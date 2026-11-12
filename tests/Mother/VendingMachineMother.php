<?php

declare(strict_types=1);

namespace Tests\Mother;

use Vending\Domain\VendingMachine;
use Vending\Domain\VendingMachineId;

final class VendingMachineMother
{
    public static function id(): VendingMachineId
    {
        return VendingMachineId::of('0193b4e2-7c2a-7d3f-8a1b-2c3d4e5f6a7b');
    }

    public static function started(): VendingMachine
    {
        return new VendingMachine(self::id());
    }
}
