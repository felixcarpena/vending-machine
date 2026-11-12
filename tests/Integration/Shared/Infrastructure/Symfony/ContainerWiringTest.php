<?php

declare(strict_types=1);

namespace Tests\Integration\Shared\Infrastructure\Symfony;

use LogicException;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\Attributes\Test;
use Shared\Application\Bus\CommandBus;
use Shared\Application\Bus\EventBus;
use Shared\Application\Bus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Application\GetMachineContents\GetMachineContents;
use Vending\Application\GetMachineContents\GetMachineContentsHandler;
use Vending\Application\StartMachine\StartMachine;
use Vending\Domain\Event\MachineServiced;
use Vending\Domain\VendingMachineRepository;

final class ContainerWiringTest extends KernelTestCase
{
    #[Test]
    public function starts_the_configured_machine_through_the_command_bus(): void
    {
        self::service(CommandBus::class)->dispatch(new StartMachine(VendingMachineMother::id()));

        $machine = self::service(VendingMachineRepository::class)->get(VendingMachineMother::id());

        self::assertEquals(VendingMachineMother::started(), $machine);
    }

    #[Test]
    public function reads_the_machine_contents_through_the_query_bus(): void
    {
        $machines = self::service(VendingMachineRepository::class);
        $machines->save(VendingMachineMother::started());
        $contentsReader = new GetMachineContentsHandler($machines);

        $contents = self::service(QueryBus::class)->ask(new GetMachineContents(VendingMachineMother::id()));

        self::assertEquals($contentsReader(new GetMachineContents(VendingMachineMother::id())), $contents);
    }

    #[Test]
    public function logs_every_command_the_command_bus_handles(): void
    {
        self::service(CommandBus::class)->dispatch(new StartMachine(VendingMachineMother::id()));

        self::assertTrue(self::service(TestHandler::class)->hasInfoThatContains('StartMachine handled in'));
    }

    #[Test]
    public function logs_every_query_the_query_bus_handles(): void
    {
        self::service(VendingMachineRepository::class)->save(VendingMachineMother::started());

        self::service(QueryBus::class)->ask(new GetMachineContents(VendingMachineMother::id()));

        self::assertTrue(self::service(TestHandler::class)->hasInfoThatContains('GetMachineContents handled in'));
    }

    #[Test]
    public function logs_every_event_the_event_bus_publishes(): void
    {
        self::service(EventBus::class)->publish(new MachineServiced(VendingMachineMother::id()));

        self::assertTrue(self::service(TestHandler::class)->hasDebugThatContains('MachineServiced handled in'));
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     */
    private static function service(string $id): object
    {
        $found = self::getContainer()->get($id);
        if (!$found instanceof $id) {
            throw new LogicException(sprintf('The container has no %s.', $id));
        }

        return $found;
    }
}
