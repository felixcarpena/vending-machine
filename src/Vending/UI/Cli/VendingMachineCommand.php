<?php

declare(strict_types=1);

namespace Vending\UI\Cli;

use Shared\Application\Bus\Command as MachineCommand;
use Shared\Application\Bus\CommandBus;
use Shared\Application\Bus\QueryBus;
use Shared\Domain\Refusal;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;
use Vending\Application\GetInsertedMoney\GetInsertedMoney;
use Vending\Application\GetMachineContents\GetMachineContents;
use Vending\Application\GetMachineContents\SlotContents;
use Vending\Application\GetOutputTray\GetOutputTray;
use Vending\Application\ServiceMachine\ServiceMachine;
use Vending\Application\StartMachine\StartMachine;
use Vending\Application\TakeOutput\TakeOutput;
use Vending\Domain\Coin;
use Vending\Domain\VendingMachineId;
use Vending\UI\Cli\Input\ActionParser;
use Vending\UI\Cli\Input\InvalidLine;
use Vending\UI\Cli\Output\CoinFormat;
use Vending\UI\Cli\Output\MoneyFormat;
use Vending\UI\Cli\Output\RefusalView;
use Vending\UI\Cli\Output\StatusView;
use Vending\UI\Cli\Output\TrayView;

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
        $slots = $this->queryBus->ask(new GetMachineContents($machineId))->slots;
        $output->writeln(sprintf('Actions: %s', implode(' | ', [
            ...array_map(CoinFormat::of(...), Coin::cases()),
            ...array_map(static fn(SlotContents $slot): string => ActionParser::BUY . $slot->selector, $slots),
            ActionParser::RETURN_COIN,
            ActionParser::SERVICE,
            ActionParser::STATUS,
            ActionParser::EXIT,
        ])));

        $lines = self::linesFrom($input);
        while (true) {
            $output->write('> ');
            $line = fgets($lines);
            if ($line === false || ActionParser::isExit($line)) {
                return Command::SUCCESS;
            }

            $this->performLine(ActionParser::actionsIn($line), $machineId, $output);

            $this->takeWhatCameOut($machineId, $output);
            $insertedMoney = $this->queryBus->ask(new GetInsertedMoney($machineId));
            $output->writeln(sprintf('  Inserted: %s', MoneyFormat::of($insertedMoney)));
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
                $message instanceof MachineCommand => $this->commandBus->dispatch($message),
                default => $output->writeln(sprintf('  Invalid input: %s', $action)),
            };
        } catch (Refusal $refusal) {
            $output->writeln(sprintf('  %s', RefusalView::for($refusal, $action)));
        }
    }

    private function takeWhatCameOut(VendingMachineId $machineId, OutputInterface $output): void
    {
        $cameOut = TrayView::of($this->queryBus->ask(new GetOutputTray($machineId)));
        if ($cameOut === null) {
            return;
        }

        $output->writeln($cameOut);
        $this->commandBus->dispatch(new TakeOutput($machineId));
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
