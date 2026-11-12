<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use DomainException;
use Vending\Domain\Selector;

final class NegativeStock extends DomainException
{
    public static function of(Selector $selector, int $stock): self
    {
        return new self(sprintf('The stock of slot %s cannot be negative, got %d.', $selector->value, $stock));
    }
}
