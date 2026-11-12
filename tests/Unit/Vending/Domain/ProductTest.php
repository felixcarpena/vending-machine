<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Exception\InvalidProduct;
use Vending\Domain\Product;

final class ProductTest extends TestCase
{
    #[Test]
    public function needs_a_name(): void
    {
        $this->expectException(InvalidProduct::class);

        Product::named('  ');
    }

    #[Test]
    public function is_named_without_surrounding_spaces(): void
    {
        $product = Product::named(' WATER ');

        self::assertSame('WATER', $product->name);
    }
}
