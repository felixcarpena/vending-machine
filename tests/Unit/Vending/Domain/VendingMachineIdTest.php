<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Exception\InvalidVendingMachineId;
use Vending\Domain\VendingMachineId;

final class VendingMachineIdTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function valuesThatAreNotAUuidV7(): iterable
    {
        yield 'nothing' => [''];
        yield 'a name' => ['machine-1'];
        yield 'a uuid v4' => ['9b2f6c1e-4d3a-4f8b-9c2d-1e5f7a8b9c0d'];
    }

    #[Test]
    #[DataProvider('valuesThatAreNotAUuidV7')]
    public function must_be_a_uuid_v7(string $value): void
    {
        $this->expectException(InvalidVendingMachineId::class);

        VendingMachineId::of($value);
    }

    #[Test]
    public function is_stored_in_lowercase(): void
    {
        $id = VendingMachineId::of('0193B4E2-7C2A-7D3F-8A1B-2C3D4E5F6A7B');

        self::assertSame('0193b4e2-7c2a-7d3f-8a1b-2c3d4e5f6a7b', $id->value);
    }
}
