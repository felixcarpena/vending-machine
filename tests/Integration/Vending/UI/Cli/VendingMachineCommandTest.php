<?php

declare(strict_types=1);

namespace Tests\Integration\Vending\UI\Cli;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class VendingMachineCommandTest extends KernelTestCase
{
    private CommandTester $session;

    protected function setUp(): void
    {
        $application = new Application(self::bootKernel());
        $this->session = new CommandTester($application->find('vending-machine'));
    }

    #[Test]
    public function greets_with_the_actions_it_accepts(): void
    {
        $this->session->setInputs(['EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString("Vending machine ready.\nActions: EXIT", $this->session->getDisplay());
    }

    #[Test]
    public function reports_anything_that_is_not_an_action_as_invalid_input(): void
    {
        $this->session->setInputs(['COFFEE, TEA', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString("Invalid input: COFFEE\n  Invalid input: TEA", $this->session->getDisplay());
    }

    #[Test]
    public function ends_the_session_on_exit(): void
    {
        $this->session->setInputs(['EXIT', 'COFFEE']);

        $this->session->execute([]);

        self::assertStringNotContainsString('COFFEE', $this->session->getDisplay());
    }

    #[Test]
    public function ends_the_session_when_the_input_ends(): void
    {
        $this->session->setInputs(['COFFEE']);

        $status = $this->session->execute([]);

        self::assertSame(Command::SUCCESS, $status);
    }
}
