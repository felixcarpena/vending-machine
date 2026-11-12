<?php

declare(strict_types=1);

namespace Tests\Application\Vending;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Double\RecordingEventBus;
use Tests\Mother\VendingMachineMother;
use Vending\Application\InsertCoin\InsertCoin;
use Vending\Application\InsertCoin\InsertCoinHandler;
use Vending\Domain\Coin;
use Vending\Domain\Event\CoinInserted;
use Vending\Domain\Money;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class InsertCoinHandlerTest extends TestCase
{
    private InMemoryVendingMachineRepository $machines;
    private RecordingEventBus $eventBus;
    private InsertCoinHandler $coinInserter;

    protected function setUp(): void
    {
        $this->machines = new InMemoryVendingMachineRepository(VendingMachineMother::withCoinsInserted());
        $this->eventBus = new RecordingEventBus();
        $this->coinInserter = new InsertCoinHandler($this->machines, $this->eventBus);
    }

    #[Test]
    public function adds_the_coin_to_the_money_inserted_in_the_machine(): void
    {
        ($this->coinInserter)(new InsertCoin(VendingMachineMother::id(), Coin::TwentyFiveCents));

        self::assertEquals(Money::fromCents(25), $this->machines->get(VendingMachineMother::id())->insertedMoney());
    }

    #[Test]
    public function publishes_that_the_coin_was_inserted(): void
    {
        ($this->coinInserter)(new InsertCoin(VendingMachineMother::id(), Coin::TwentyFiveCents));

        self::assertEquals(
            [new CoinInserted(VendingMachineMother::id(), Coin::TwentyFiveCents)],
            $this->eventBus->published,
        );
    }
}
