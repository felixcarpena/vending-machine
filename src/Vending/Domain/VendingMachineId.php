<?php

declare(strict_types=1);

namespace Vending\Domain;

use Vending\Domain\Exception\InvalidVendingMachineId;

final readonly class VendingMachineId
{
    private const string UUID_V7 = '/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    private function __construct(public string $value)
    {
        if (preg_match(self::UUID_V7, $value) !== 1) {
            throw InvalidVendingMachineId::becauseItIsNotAUuidV7($value);
        }
    }

    public static function of(string $value): self
    {
        return new self(strtolower($value));
    }
}
