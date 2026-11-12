<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use DomainException;
use Vending\Domain\Money;
use Vending\Domain\Selector;

final class InvalidPrice extends DomainException
{
    public static function of(Selector $selector, Money $price, Money $step): self
    {
        return new self(sprintf(
            'The price of slot %s must be above zero and a multiple of %d cents, got %d cents.',
            $selector->value,
            $step->cents,
            $price->cents,
        ));
    }
}
