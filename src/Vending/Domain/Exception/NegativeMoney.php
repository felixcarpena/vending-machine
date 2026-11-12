<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use DomainException;

final class NegativeMoney extends DomainException
{
    public static function of(int $cents): self
    {
        return new self(sprintf('Money cannot be negative, got %d cents.', $cents));
    }
}
