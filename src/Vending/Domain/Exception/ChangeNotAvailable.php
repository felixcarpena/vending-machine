<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use Shared\Domain\Refusal;
use Vending\Domain\Money;

final class ChangeNotAvailable extends Refusal
{
    public static function for(Money $change): self
    {
        return new self(sprintf('%d cents of change cannot be paid.', $change->cents));
    }
}
