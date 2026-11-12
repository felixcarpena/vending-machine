<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use DomainException;

final class InvalidProduct extends DomainException
{
    public static function becauseItHasNoName(): self
    {
        return new self('A product needs a name.');
    }
}
