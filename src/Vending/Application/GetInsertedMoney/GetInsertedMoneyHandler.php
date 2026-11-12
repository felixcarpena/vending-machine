<?php

declare(strict_types=1);

namespace Vending\Application\GetInsertedMoney;

use Vending\Domain\Money;
use Vending\Domain\VendingMachineRepository;

final readonly class GetInsertedMoneyHandler
{
    public function __construct(private VendingMachineRepository $machines) {}

    public function __invoke(GetInsertedMoney $query): Money
    {
        return $this->machines->get($query->machineId)->insertedMoney();
    }
}
