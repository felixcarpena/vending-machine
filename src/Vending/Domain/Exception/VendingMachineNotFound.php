<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use DomainException;
use Vending\Domain\VendingMachineId;

final class VendingMachineNotFound extends DomainException
{
    public static function withId(VendingMachineId $id): self
    {
        return new self(sprintf('There is no vending machine "%s".', $id->value));
    }
}
