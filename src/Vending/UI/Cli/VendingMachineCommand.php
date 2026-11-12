<?php

declare(strict_types=1);

namespace Vending\UI\Cli;

use Shared\Application\Bus\CommandBus;
use Shared\Application\Bus\QueryBus;
use Shared\Domain\Refusal;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use Vending\Application\GetMachineContents\GetMachineContents;
use Vending\Application\ServiceMachine\ServiceMachine;
use Vending\Application\StartMachine\StartMachine;
use Vending\Domain\VendingMachineId;
use Vending\UI\Cli\Input\ActionParser;
use Vending\UI\Cli\Input\InvalidLine;
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
        $output->writeln(sprintf(
            'Actions: %s | %s | %s',
            ActionParser::SERVICE,
            ActionParser::STATUS,
            ActionParser::EXIT,
        ));

        $lines = self::linesFrom($input);
        while (true) {
            $output->write('> ');
            $line = fgets($lines);
            if ($line === false || ActionParser::isExit($line)) {
                return Command::SUCCESS;
            }

            $this->performLine(ActionParser::actionsIn($line), $machineId, $output);
        }
    }

    /** @param list<string>|InvalidLine $actions */
    private function performLine(array|InvalidLine $actions, VendingMachineId $machineId, OutputInterface $output): void
    {
        if ($actions instanceof InvalidLine) {
            $output->writeln(sprintf('  Invalid input: %s', $actions->line));

            return;
        }

        foreach ($actions as $action) {
            $this->perform($action, $machineId, $output);
        }
    }

    private function perform(string $action, VendingMachineId $machineId, OutputInterface $output): void
    {
        $message = ActionParser::messageFor($action, $machineId);

        try {
            match (true) {
                $message instanceof GetMachineContents => $this->showContents($message, $output),
                $message instanceof ServiceMachine => $this->service($message, $output),
                default => $output->writeln(sprintf('  Invalid input: %s', $action)),
            };
        } catch (Refusal) {
            $output->writeln(sprintf('  Invalid input: %s', $action));
        }
    }

    private function service(ServiceMachine $servicing, OutputInterface $output): void
    {
        $this->commandBus->dispatch($servicing);
        $output->writeln('  Machine serviced.');
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
