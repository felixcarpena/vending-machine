<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\UI\Cli\Input;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Application\GetMachineContents\GetMachineContents;
use Vending\Application\InsertCoin\InsertCoin;
use Vending\Application\ReturnMoney\ReturnMoney;
use Vending\Application\ServiceMachine\ServiceMachine;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\StockLevels;
use Vending\UI\Cli\Input\ActionParser;
use Vending\UI\Cli\Input\InvalidLine;

final class ActionParserTest extends TestCase
{
    #[Test]
    public function splits_a_line_into_its_actions(): void
    {
        $actions = ActionParser::actionsIn("0.25, 1, GET-WATER\n");

        self::assertSame(['0.25', '1', 'GET-WATER'], $actions);
    }

    #[Test]
    public function skips_empty_actions(): void
    {
        $actions = ActionParser::actionsIn(' , 1,, ');

        self::assertSame(['1'], $actions);
    }

    #[Test]
    #[DataProvider('serviceLinesWithAComma')]
    public function reads_a_service_line_with_a_comma_as_invalid(string $line): void
    {
        self::assertEquals(new InvalidLine(trim($line)), ActionParser::actionsIn($line));
    }

    /** @return array<string, array{string}> */
    public static function serviceLinesWithAComma(): array
    {
        return [
            'another action after it' => ["SERVICE WATER=1, 0.05=2\n"],
            'another action before it' => ["0.25, SERVICE WATER=1\n"],
            'a comma at the end' => ["SERVICE WATER=1,\n"],
        ];
    }

    #[Test]
    public function reads_a_service_alone_on_its_line_as_one_action(): void
    {
        self::assertSame(['SERVICE WATER=1'], ActionParser::actionsIn(" SERVICE WATER=1 \n"));
    }

    #[Test]
    #[DataProvider('exitLines')]
    public function recognises_the_exit_line(string $line): void
    {
        self::assertTrue(ActionParser::isExit($line));
    }

    /** @return array<string, array{string}> */
    public static function exitLines(): array
    {
        return [
            'alone' => ['EXIT'],
            'with a line break' => ["EXIT\n"],
            'with spaces' => ['  EXIT  '],
        ];
    }

    #[Test]
    public function does_not_exit_when_exit_comes_with_other_actions(): void
    {
        self::assertFalse(ActionParser::isExit('1, EXIT'));
    }

    #[Test]
    public function reads_status_as_asking_for_the_machine_contents(): void
    {
        $message = ActionParser::messageFor('STATUS', VendingMachineMother::id());

        self::assertEquals(new GetMachineContents(VendingMachineMother::id()), $message);
    }

    #[Test]
    public function reads_nothing_from_an_unknown_action(): void
    {
        self::assertNull(ActionParser::messageFor('COFFEE', VendingMachineMother::id()));
    }

    #[Test]
    public function reads_the_stock_of_each_slot_in_a_service(): void
    {
        $message = ActionParser::messageFor('SERVICE WATER=5 SODA1=0', VendingMachineMother::id());

        self::assertEquals(
            new ServiceMachine(VendingMachineMother::id(), StockLevels::of(['WATER' => 5, 'SODA1' => 0]), Coins::of()),
            $message,
        );
    }

    #[Test]
    public function reads_the_number_of_each_coin_in_a_service(): void
    {
        $message = ActionParser::messageFor('SERVICE 0.25=2 1=1', VendingMachineMother::id());

        self::assertEquals(
            new ServiceMachine(
                VendingMachineMother::id(),
                StockLevels::of([]),
                Coins::of(Coin::TwentyFiveCents, Coin::TwentyFiveCents, Coin::OneHundredCents),
            ),
            $message,
        );
    }

    /** @return iterable<string, array{string}> */
    public static function servicesThatAreNotValid(): iterable
    {
        yield 'a negative count' => ['SERVICE WATER=-1'];
        yield 'a count in words' => ['SERVICE WATER=ten'];
        yield 'a coin the machine does not take' => ['SERVICE 0.50=2'];
        yield 'a lower case selector' => ['SERVICE water=2'];
        yield 'no space after the action' => ['SERVICEWATER=2'];
        yield 'nothing to service' => ['SERVICE'];
    }

    #[Test]
    #[DataProvider('servicesThatAreNotValid')]
    public function reads_nothing_from_a_service_that_is_not_valid(string $action): void
    {
        self::assertNull(ActionParser::messageFor($action, VendingMachineMother::id()));
    }

    /** @return iterable<string, array{string, Coin}> */
    public static function writtenCoins(): iterable
    {
        yield '0.05' => ['0.05', Coin::FiveCents];
        yield '0.10' => ['0.10', Coin::TenCents];
        yield '0.25' => ['0.25', Coin::TwentyFiveCents];
        yield '1' => ['1', Coin::OneHundredCents];
    }

    #[Test]
    #[DataProvider('writtenCoins')]
    public function reads_a_coin_as_inserting_it(string $action, Coin $coin): void
    {
        $message = ActionParser::messageFor($action, VendingMachineMother::id());

        self::assertEquals(new InsertCoin(VendingMachineMother::id(), $coin), $message);
    }

    #[Test]
    public function reads_nothing_from_a_coin_the_machine_does_not_take(): void
    {
        self::assertNull(ActionParser::messageFor('0.50', VendingMachineMother::id()));
    }

    #[Test]
    public function reads_return_coin_as_returning_the_money(): void
    {
        $message = ActionParser::messageFor('RETURN-COIN', VendingMachineMother::id());

        self::assertEquals(new ReturnMoney(VendingMachineMother::id()), $message);
    }
}
