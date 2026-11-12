<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use DomainException;

final class InvalidVendingMachineId extends DomainException
{
    public static function becauseItIsNotAUuidV7(string $value): self
    {
        return new self(sprintf('A vending machine id must be a UUID v7, got "%s".', $value));
    }
}
