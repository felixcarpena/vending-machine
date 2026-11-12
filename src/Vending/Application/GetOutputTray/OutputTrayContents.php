<?php

declare(strict_types=1);

namespace Vending\Application\GetOutputTray;

use Vending\Domain\Coins;

final readonly class OutputTrayContents
{
    public function __construct(public Coins $coins) {}
}
