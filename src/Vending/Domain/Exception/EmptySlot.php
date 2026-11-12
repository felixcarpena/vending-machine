<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use Shared\Domain\Refusal;
use Vending\Domain\Selector;

final class EmptySlot extends Refusal
{
    private function __construct(public readonly Selector $selector)
    {
        parent::__construct(sprintf('Slot %s is empty.', $selector->value));
    }

    public static function of(Selector $selector): self
    {
        return new self($selector);
    }
}
