<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\UI\Cli\Output;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Money;
use Vending\UI\Cli\Output\MoneyFormat;

final class MoneyFormatTest extends TestCase
{
    /** @return iterable<string, array{int, string}> */
    public static function writtenAmounts(): iterable
    {
        yield 'nothing' => [0, '0.00'];
        yield 'cents only' => [5, '0.05'];
        yield 'one unit' => [100, '1.00'];
        yield 'units and cents' => [198, '1.98'];
    }

    #[Test]
    #[DataProvider('writtenAmounts')]
    public function writes_an_amount_with_two_decimals(int $cents, string $written): void
    {
        self::assertSame($written, MoneyFormat::of(Money::fromCents($cents)));
    }
}
