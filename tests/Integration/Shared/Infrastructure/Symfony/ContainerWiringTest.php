<?php

declare(strict_types=1);

namespace Tests\Integration\Shared\Infrastructure\Symfony;

use LogicException;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\Attributes\Test;
use Shared\Application\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Application\StartMachine\StartMachine;
use Vending\Domain\VendingMachineRepository;

final class ContainerWiringTest extends KernelTestCase
{
    #[Test]
    public function wires_starting_a_machine_through_the_command_bus(): void
    {
        self::service(CommandBus::class)->dispatch(new StartMachine(VendingMachineMother::id()));

        $machine = self::service(VendingMachineRepository::class)->get(VendingMachineMother::id());

        self::assertEquals(VendingMachineMother::started(), $machine);
    }

    #[Test]
    public function logs_every_command_the_command_bus_handles(): void
    {
        self::service(CommandBus::class)->dispatch(new StartMachine(VendingMachineMother::id()));

        self::assertTrue(self::service(TestHandler::class)->hasInfoThatContains('StartMachine handled in'));
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
