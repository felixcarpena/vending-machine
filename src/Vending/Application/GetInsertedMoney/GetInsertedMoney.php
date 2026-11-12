<?php

declare(strict_types=1);

namespace Vending\Application\GetInsertedMoney;

use Shared\Application\Bus\Query;
use Vending\Domain\Money;
use Vending\Domain\VendingMachineId;

/** @implements Query<Money> */
final readonly class GetInsertedMoney implements Query
{
    public function __construct(public VendingMachineId $machineId) {}
}
