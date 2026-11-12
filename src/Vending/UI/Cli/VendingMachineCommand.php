<?php

declare(strict_types=1);

namespace Vending\UI\Cli;

use Shared\Application\Bus\CommandBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use Vending\Application\StartMachine\StartMachine;
use Vending\Domain\VendingMachineId;
use Vending\UI\Cli\Input\ActionParser;

#[AsCommand(name: 'vending-machine', description: 'Runs an interactive vending machine session.')]
final class VendingMachineCommand extends Command
{
    public function __construct(private readonly CommandBus $commandBus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $machineId = VendingMachineId::of(Uuid::v7()->toRfc4122());
        $this->commandBus->dispatch(new StartMachine($machineId));

        $output->writeln('Vending machine ready.');
        $output->writeln('Actions: ' . ActionParser::EXIT);

        $lines = self::linesFrom($input);
        while (true) {
            $output->write('> ');
            $line = fgets($lines);
            if ($line === false || ActionParser::isExit($line)) {
                return Command::SUCCESS;
            }

            foreach (ActionParser::actionsIn($line) as $action) {
                $output->writeln(sprintf('  Invalid input: %s', $action));
            }
        }
    }

    /** @return resource */
    private static function linesFrom(InputInterface $input)
    {
        if ($input instanceof StreamableInputInterface && $input->getStream() !== null) {
            return $input->getStream();
        }

        return STDIN;
    }
}
