<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use Shared\Domain\Refusal;
use Vending\Domain\Selector;

final class UnknownSlot extends Refusal
{
    public static function of(Selector $selector): self
    {
        return new self(sprintf('The machine has no slot %s.', $selector->value));
    }
}
