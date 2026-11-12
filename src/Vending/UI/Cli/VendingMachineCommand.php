<?php

declare(strict_types=1);

namespace Vending\UI\Cli;

use Shared\Application\Bus\CommandBus;
use Shared\Application\Bus\QueryBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use Vending\Application\GetMachineContents\GetMachineContents;
use Vending\Application\StartMachine\StartMachine;
use Vending\Domain\VendingMachineId;
use Vending\UI\Cli\Input\ActionParser;
use Vending\UI\Cli\Output\StatusView;

#[AsCommand(name: 'vending-machine', description: 'Runs an interactive vending machine session.')]
final class VendingMachineCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly QueryBus $queryBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $machineId = VendingMachineId::of(Uuid::v7()->toRfc4122());
        $this->commandBus->dispatch(new StartMachine($machineId));

        $output->writeln('Vending machine ready.');
        $output->writeln(sprintf('Actions: %s | %s', ActionParser::STATUS, ActionParser::EXIT));

        $lines = self::linesFrom($input);
        while (true) {
            $output->write('> ');
            $line = fgets($lines);
            if ($line === false || ActionParser::isExit($line)) {
                return Command::SUCCESS;
            }

            foreach (ActionParser::actionsIn($line) as $action) {
                $message = ActionParser::messageFor($action, $machineId);
                match (true) {
                    $message instanceof GetMachineContents => $this->showContents($message, $output),
                    default => $output->writeln(sprintf('  Invalid input: %s', $action)),
                };
            }
        }
    }

    private function showContents(GetMachineContents $query, OutputInterface $output): void
    {
        foreach (StatusView::of($this->queryBus->ask($query)) as $line) {
            $output->writeln(sprintf('  %s', $line));
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
