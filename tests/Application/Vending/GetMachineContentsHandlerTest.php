<?php

declare(strict_types=1);

namespace Tests\Application\Vending;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Application\GetMachineContents\GetMachineContents;
use Vending\Application\GetMachineContents\GetMachineContentsHandler;
use Vending\Application\GetMachineContents\MachineContents;
use Vending\Application\GetMachineContents\SlotContents;
use Vending\Domain\Money;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class GetMachineContentsHandlerTest extends TestCase
{
    #[Test]
    public function lists_each_slot_with_its_product_price_and_stock(): void
    {
        $contentsReader = new GetMachineContentsHandler(
            new InMemoryVendingMachineRepository(VendingMachineMother::started()),
        );

        $contents = $contentsReader(new GetMachineContents(VendingMachineMother::id()));

        self::assertEquals(new MachineContents([
            new SlotContents('WATER', 'WATER', Money::fromCents(65), 10),
            new SlotContents('JUICE', 'JUICE', Money::fromCents(100), 10),
            new SlotContents('SODA', 'SODA', Money::fromCents(150), 10),
        ]), $contents);
    }
}
