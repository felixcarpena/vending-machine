<?php

declare(strict_types=1);

namespace Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\AfterScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;

final class VendingMachineContext implements Context
{
    private ?CliSession $session = null;

    /** @var array<string, array{product: string, price: string}> */
    private array $catalog = [];

    /** @var array<string, string> */
    private array $stock = [];

    /** @var array<string, string> */
    private array $coins = [];

    private string $lastResponse = '';

    #[Given('the machine has these slots:')]
    public function theMachineHasTheseSlots(TableNode $slots): void
    {
        $this->catalog = [];
        $this->stock = [];
        foreach ($slots->getColumnsHash() as $slot) {
            $this->catalog[$slot['slot']] = ['product' => $slot['product'], 'price' => $slot['price']];
            $this->stock[$slot['slot']] = $slot['stock'];
        }
    }

    #[Given('the machine holds these coins:')]
    public function theMachineHoldsTheseCoins(TableNode $coins): void
    {
        $this->coins = [];
        foreach ($coins->getColumnsHash() as $coin) {
            $this->coins[$coin['coin']] = $coin['count'];
        }
    }

    #[Given('the machine holds no coins')]
    public function theMachineHoldsNoCoins(): void
    {
        $this->coins = [];
    }

    #[When('/^the (?:customer|service person) enters "([^"]*)"$/')]
    #[Given('/^the (?:customer|service person) entered "([^"]*)"$/')]
    public function entersALine(string $line): void
    {
        $this->lastResponse = $this->session()->send($line);
    }

    #[Then('the status shows these slots:')]
    public function theStatusShowsTheseSlots(TableNode $slots): void
    {
        Assert::assertSame($slots->getColumnsHash(), self::slotsIn($this->lastResponse));
    }

    #[Then('the status shows these coins:')]
    public function theStatusShowsTheseCoins(TableNode $coins): void
    {
        Assert::assertSame($coins->getColumnsHash(), self::coinsIn($this->lastResponse));
    }

    #[Then('the machine now has these slots:')]
    public function theMachineNowHasTheseSlots(TableNode $slots): void
    {
        Assert::assertSame($slots->getColumnsHash(), self::slotsIn($this->session()->send('STATUS')));
    }

    #[Then('the machine now holds these coins:')]
    public function theMachineNowHoldsTheseCoins(TableNode $coins): void
    {
        Assert::assertSame($coins->getColumnsHash(), self::coinsIn($this->session()->send('STATUS')));
    }

    #[Then('the machine gives out :items')]
    public function theMachineGivesOut(string $items): void
    {
        Assert::assertSame([$items], self::handedOutIn($this->lastResponse));
    }

    #[Then('nothing comes out')]
    public function nothingComesOut(): void
    {
        Assert::assertSame([], self::handedOutIn($this->lastResponse));
    }

    #[Then('the inserted money is :amount')]
    public function theInsertedMoneyIs(string $amount): void
    {
        preg_match_all('/^  Inserted: (?<amount>\d+\.\d{2})$/m', $this->lastResponse, $lines);

        Assert::assertSame($amount, end($lines['amount']));
    }

    #[Then('the input :input is rejected as invalid')]
    public function theInputIsRejectedAsInvalid(string $input): void
    {
        Assert::assertStringContainsString(sprintf('Invalid input: %s', $input), $this->lastResponse);
    }

    #[AfterScenario]
    public function endTheSession(): void
    {
        $this->session?->stop();
        $this->session = null;
    }

    private function session(): CliSession
    {
        if ($this->session === null) {
            $session = CliSession::start(dirname(__DIR__, 2));
            $this->prepareTheMachine($session);
            $this->session = $session;
        }

        return $this->session;
    }

    private function prepareTheMachine(CliSession $session): void
    {
        if ($this->stock === []) {
            return;
        }

        $quantities = array_map(
            static fn(string $item, string $count): string => sprintf('%s=%s', $item, $count),
            [...array_keys($this->stock), ...array_keys($this->coins)],
            [...array_values($this->stock), ...array_values($this->coins)],
        );
        $session->send('SERVICE ' . implode(' ', $quantities));

        $catalog = [];
        foreach (self::slotsIn($session->send('STATUS')) as $slot) {
            $catalog[$slot['slot']] = ['product' => $slot['product'], 'price' => $slot['price']];
        }
        Assert::assertSame($this->catalog, array_intersect_key($catalog, $this->catalog), 'The catalog differs.');
    }

    /** @return list<string> */
    private static function handedOutIn(string $response): array
    {
        preg_match_all('/^-> (?<items>.+)$/m', $response, $lines);

        return $lines['items'];
    }

    /** @return list<array{slot: string, product: string, price: string, stock: string}> */
    private static function slotsIn(string $response): array
    {
        preg_match_all(
            '/^  (?<slot>\S+) holds (?<product>.+) at (?<price>\d+\.\d{2}) \((?<stock>\d+) left\)$/m',
            $response,
            $lines,
        );

        return array_map(
            static fn(string $slot, string $product, string $price, string $stock): array => compact(
                'slot',
                'product',
                'price',
                'stock',
            ),
            $lines['slot'],
            $lines['product'],
            $lines['price'],
            $lines['stock'],
        );
    }

    /** @return list<array{coin: string, count: string}> */
    private static function coinsIn(string $response): array
    {
        preg_match('/^  Coins: (?<coins>.+)$/m', $response, $line);
        preg_match_all('/(?<coin>[\d.]+) x(?<count>\d+)/', $line['coins'] ?? '', $coins);

        return array_map(
            static fn(string $coin, string $count): array => compact('coin', 'count'),
            $coins['coin'],
            $coins['count'],
        );
    }
}
