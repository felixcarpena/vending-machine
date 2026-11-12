<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use DomainException;

final class InvalidSelector extends DomainException
{
    public static function becauseItIsEmpty(): self
    {
        return new self('A selector cannot be empty.');
    }
}
