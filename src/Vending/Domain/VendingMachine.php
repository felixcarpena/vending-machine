<?php

declare(strict_types=1);

namespace Vending\Domain;

use Shared\Domain\AggregateRoot;
use Vending\Domain\Event\CoinInserted;
use Vending\Domain\Event\CoinsReturned;
use Vending\Domain\Event\MachineServiced;
use Vending\Domain\Event\OutputTaken;

final class VendingMachine extends AggregateRoot
{
    private Escrow $escrow;

    public private(set) OutputTray $outputTray;

    public function __construct(
        public readonly VendingMachineId $id,
        public private(set) AvailableChange $availableChange,
        public private(set) Slots $slots,
    ) {
        $this->escrow = Escrow::empty();
        $this->outputTray = OutputTray::empty();
    }

    public function insertCoin(Coin $coin): void
    {
        $this->escrow = $this->escrow->insert($coin);
        $this->record(new CoinInserted($this->id, $coin));
    }

    public function returnMoney(): void
    {
        if ($this->escrow->isEmpty()) {
            return;
        }

        $this->outputTray = $this->outputTray->withCoins($this->escrow->coins);
        $this->record(new CoinsReturned($this->id, $this->escrow->coins));
        $this->escrow = Escrow::empty();
    }

    public function takeOutput(): void
    {
        if ($this->outputTray->isEmpty()) {
            return;
        }

        $this->outputTray = OutputTray::empty();
        $this->record(new OutputTaken($this->id));
    }

    public function insertedMoney(): Money
    {
        return $this->escrow->total();
    }

    public function service(StockLevels $stock, Coins $change): void
    {
        $this->slots = $this->slots->restockedTo($stock);
        $this->availableChange = AvailableChange::of($change);
        $this->record(new MachineServiced($this->id));
    }
}
