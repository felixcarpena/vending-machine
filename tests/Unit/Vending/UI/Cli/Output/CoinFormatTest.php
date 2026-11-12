<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\UI\Cli\Output;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Coin;
use Vending\UI\Cli\Output\CoinFormat;

final class CoinFormatTest extends TestCase
{
    /** @return iterable<string, array{Coin, string}> */
    public static function writtenCoins(): iterable
    {
        yield '0.05' => [Coin::FiveCents, '0.05'];
        yield '0.10' => [Coin::TenCents, '0.10'];
        yield '0.25' => [Coin::TwentyFiveCents, '0.25'];
        yield '1' => [Coin::OneHundredCents, '1'];
    }

    #[Test]
    #[DataProvider('writtenCoins')]
    public function writes_a_coin_as_the_statement_does(Coin $coin, string $written): void
    {
        self::assertSame($written, CoinFormat::of($coin));
    }
}
