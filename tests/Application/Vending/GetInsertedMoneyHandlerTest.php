<?php

declare(strict_types=1);

namespace Tests\Application\Vending;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Application\GetInsertedMoney\GetInsertedMoney;
use Vending\Application\GetInsertedMoney\GetInsertedMoneyHandler;
use Vending\Domain\Coin;
use Vending\Domain\Money;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class GetInsertedMoneyHandlerTest extends TestCase
{
    #[Test]
    public function tells_how_much_money_is_inserted(): void
    {
        $machines = new InMemoryVendingMachineRepository(
            VendingMachineMother::withCoinsInserted(Coin::OneHundredCents, Coin::FiveCents),
        );
        $insertedMoneyReader = new GetInsertedMoneyHandler($machines);

        $insertedMoney = $insertedMoneyReader(new GetInsertedMoney(VendingMachineMother::id()));

        self::assertEquals(Money::fromCents(105), $insertedMoney);
    }
}
