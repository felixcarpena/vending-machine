<?php

declare(strict_types=1);

namespace Tests\Application\Vending;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Application\GetOutputTray\GetOutputTray;
use Vending\Application\GetOutputTray\GetOutputTrayHandler;
use Vending\Application\GetOutputTray\OutputTrayContents;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class GetOutputTrayHandlerTest extends TestCase
{
    #[Test]
    public function tells_what_is_in_the_output_tray(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::TenCents, Coin::OneHundredCents);
        $machine->returnMoney();
        $trayReader = new GetOutputTrayHandler(new InMemoryVendingMachineRepository($machine));

        $tray = $trayReader(new GetOutputTray(VendingMachineMother::id()));

        self::assertEquals(new OutputTrayContents(Coins::of(Coin::TenCents, Coin::OneHundredCents)), $tray);
    }
}
