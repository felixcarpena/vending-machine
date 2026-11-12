<?php

declare(strict_types=1);

namespace Tests\Application\Vending;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Double\RecordingEventBus;
use Tests\Mother\VendingMachineMother;
use Vending\Application\BuyProduct\BuyProduct;
use Vending\Application\BuyProduct\BuyProductHandler;
use Vending\Domain\Coin;
use Vending\Domain\Event\ProductDispensed;
use Vending\Domain\FewestCoinsChangeMaker;
use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class BuyProductHandlerTest extends TestCase
{
    private InMemoryVendingMachineRepository $machines;
    private RecordingEventBus $eventBus;
    private BuyProductHandler $productSeller;

    protected function setUp(): void
    {
        $this->machines = new InMemoryVendingMachineRepository(
            VendingMachineMother::withCoinsInserted(Coin::OneHundredCents),
        );
        $this->eventBus = new RecordingEventBus();
        $this->productSeller = new BuyProductHandler($this->machines, $this->eventBus, new FewestCoinsChangeMaker());
    }

    #[Test]
    public function saves_the_machine_after_the_sale(): void
    {
        ($this->productSeller)(new BuyProduct(VendingMachineMother::id(), Selector::of('JUICE')));

        $expected = VendingMachineMother::withCoinsInserted(Coin::OneHundredCents);
        $expected->buy(Selector::of('JUICE'), new FewestCoinsChangeMaker());
        $expected->pullEvents();
        self::assertEquals($expected, $this->machines->get(VendingMachineMother::id()));
    }

    #[Test]
    public function publishes_that_the_product_was_dispensed(): void
    {
        ($this->productSeller)(new BuyProduct(VendingMachineMother::id(), Selector::of('JUICE')));

        self::assertEquals(
            [new ProductDispensed(VendingMachineMother::id(), Selector::of('JUICE'), Product::named('JUICE'), Money::fromCents(100))],
            $this->eventBus->published,
        );
    }
}
