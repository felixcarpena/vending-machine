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
            "Vending machine ready.\n"
            . 'Actions: 0.05 | 0.10 | 0.25 | 1 | GET-WATER | GET-JUICE | GET-SODA | '
            . 'RETURN-COIN | SERVICE | STATUS | EXIT',
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
    public function services_the_machine(): void
    {
        $this->session->setInputs(['SERVICE WATER=5 0.25=1', 'STATUS', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString(
            "  WATER holds WATER at 0.65 (5 left)\n"
            . "  JUICE holds JUICE at 1.00 (0 left)\n"
            . "  SODA holds SODA at 1.50 (0 left)\n"
            . '  Coins: 0.05 x0, 0.10 x0, 0.25 x1, 1 x0',
            $this->session->getDisplay(),
        );
    }

    #[Test]
    public function confirms_a_service(): void
    {
        $this->session->setInputs(['SERVICE WATER=5', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString('  Machine serviced.', $this->session->getDisplay());
    }

    #[Test]
    public function reports_a_service_it_cannot_carry_out_as_invalid_input(): void
    {
        $this->session->setInputs(['SERVICE COFFEE=3', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString('  Invalid input: SERVICE COFFEE=3', $this->session->getDisplay());
    }

    #[Test]
    public function reports_a_service_line_with_a_comma_as_invalid_input_as_a_whole(): void
    {
        $this->session->setInputs(['SERVICE WATER=1, 0.05=2', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString('  Invalid input: SERVICE WATER=1, 0.05=2', $this->session->getDisplay());
        self::assertStringNotContainsString('Machine serviced.', $this->session->getDisplay());
    }

    #[Test]
    public function ends_every_line_with_the_inserted_money(): void
    {
        $this->session->setInputs(['0.25, 0.10', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString('  Inserted: 0.35', $this->session->getDisplay());
    }

    #[Test]
    public function hands_back_the_inserted_coins_largest_first(): void
    {
        $this->session->setInputs(['0.10, 1, RETURN-COIN', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString('-> 1, 0.10', $this->session->getDisplay());
    }

    #[Test]
    public function shows_what_came_out_only_once(): void
    {
        $this->session->setInputs(['0.10, RETURN-COIN', 'STATUS', 'EXIT']);

        $this->session->execute([]);

        self::assertSame(1, substr_count($this->session->getDisplay(), '-> 0.10'));
    }

    #[Test]
    public function hands_out_the_product_bought(): void
    {
        $this->session->setInputs(['1, 0.25, 0.25, GET-SODA', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString('-> SODA', $this->session->getDisplay());
    }

    #[Test]
    public function explains_why_a_purchase_is_refused(): void
    {
        $this->session->setInputs(['0.25, GET-JUICE', 'EXIT']);

        $this->session->execute([]);

        self::assertStringContainsString(
            '  Purchase refused: not enough money (price 1.00, inserted 0.25)',
            $this->session->getDisplay(),
        );
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
