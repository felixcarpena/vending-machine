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

        self::assertStringContainsString(
            "Vending machine ready.\nActions: STATUS | EXIT",
            $this->session->getDisplay(),
        );
    }

    #[Test]
    public function reports_anything_that_is_not_an_action_as_invalid_input(): void
    {
        $this->session->setInputs(['COFFEE, TEA', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString("Invalid input: COFFEE\n  Invalid input: TEA", $this->session->getDisplay());
    }

    #[Test]
    public function shows_each_slot_with_its_product_price_and_stock_on_status(): void
    {
        $this->session->setInputs(['STATUS', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString(
            "  WATER holds WATER at 0.65 (10 left)\n"
            . "  JUICE holds JUICE at 1.00 (10 left)\n"
            . "  SODA holds SODA at 1.50 (10 left)",
            $this->session->getDisplay(),
        );
    }

    #[Test]
    public function shows_how_many_of_each_coin_the_machine_holds_on_status(): void
    {
        $this->session->setInputs(['STATUS', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString('  Coins: 0.05 x10, 0.10 x10, 0.25 x10, 1 x0', $this->session->getDisplay());
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
