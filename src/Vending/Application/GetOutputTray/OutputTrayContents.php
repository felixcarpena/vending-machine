<?php

declare(strict_types=1);

namespace Vending\Application\GetOutputTray;

use Vending\Domain\Coins;

final readonly class OutputTrayContents
{
    /** @param list<string> $products */
    public function __construct(
        public array $products,
        public Coins $coins,
    ) {}
}
