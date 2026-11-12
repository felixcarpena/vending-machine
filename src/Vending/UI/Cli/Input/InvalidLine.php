<?php

declare(strict_types=1);

namespace Vending\UI\Cli\Input;

final readonly class InvalidLine
{
    public function __construct(public string $line) {}
}
