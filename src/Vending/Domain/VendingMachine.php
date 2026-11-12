<?php

declare(strict_types=1);

namespace Vending\Domain;

use Shared\Domain\AggregateRoot;
use Vending\Domain\Event\CoinInserted;
use Vending\Domain\Event\CoinsReturned;
use Vending\Domain\Event\MachineServiced;
use Vending\Domain\Event\OutputTaken;
use Vending\Domain\Event\ProductDispensed;
use Vending\Domain\Exception\ChangeNotAvailable;
use Vending\Domain\Exception\NotEnoughMoney;

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

    public function buy(Selector $selector): void
    {
        $slot = $this->slots->find($selector);
        $slotsAfterSale = $this->slots->withOneUnitLessOf($selector);

        $inserted = $this->escrow->total();
        if ($inserted->isLessThan($slot->price)) {
            throw NotEnoughMoney::for($slot->price, $inserted);
        }
        $change = $inserted->subtract($slot->price);
        if (Money::zero()->isLessThan($change)) {
            throw ChangeNotAvailable::for($change);
        }

        $this->slots = $slotsAfterSale;
        $this->availableChange = $this->availableChange->deposit($this->escrow->coins);
        $this->escrow = Escrow::empty();
        $this->outputTray = $this->outputTray->withProduct($slot->product);
        $this->record(new ProductDispensed($this->id, $selector, $slot->product, $slot->price));
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
