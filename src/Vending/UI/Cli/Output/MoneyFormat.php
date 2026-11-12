<?php

declare(strict_types=1);

namespace Vending\UI\Cli\Output;

use Vending\Domain\Money;

final class MoneyFormat
{
    public static function of(Money $money): string
    {
        return sprintf('%d.%02d', intdiv($money->cents, 100), $money->cents % 100);
    }
}
