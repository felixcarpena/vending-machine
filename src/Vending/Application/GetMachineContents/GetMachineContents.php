<?php

declare(strict_types=1);

namespace Vending\Application\GetMachineContents;

use Shared\Application\Bus\Query;
use Vending\Domain\VendingMachineId;

/** @implements Query<MachineContents> */
final readonly class GetMachineContents implements Query
{
    public function __construct(public VendingMachineId $machineId) {}
}
