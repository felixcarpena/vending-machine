<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Exception\InvalidSelector;
use Vending\Domain\Selector;

final class SelectorTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function emptyValues(): iterable
    {
        yield 'nothing' => [''];
        yield 'only spaces' => ['   '];
    }

    #[Test]
    #[DataProvider('emptyValues')]
    public function cannot_be_empty(string $value): void
    {
        $this->expectException(InvalidSelector::class);

        Selector::of($value);
    }

    #[Test]
    public function is_kept_in_capital_letters_without_surrounding_spaces(): void
    {
        $selector = Selector::of(' water1 ');

        self::assertSame('WATER1', $selector->value);
    }

    #[Test]
    public function is_written_as_its_value(): void
    {
        self::assertSame('WATER', (string) Selector::of('WATER'));
    }
}
