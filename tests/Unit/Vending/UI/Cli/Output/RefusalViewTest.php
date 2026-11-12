<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\UI\Cli\Output;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shared\Domain\Refusal;
use Vending\Domain\Exception\ChangeNotAvailable;
use Vending\Domain\Exception\EmptySlot;
use Vending\Domain\Exception\NotEnoughMoney;
use Vending\Domain\Exception\UnknownSlot;
use Vending\Domain\Money;
use Vending\Domain\Selector;
use Vending\UI\Cli\Output\RefusalView;

final class RefusalViewTest extends TestCase
{
    /** @return iterable<string, array{Refusal, string}> */
    public static function refusals(): iterable
    {
        yield 'not enough money' => [
            NotEnoughMoney::for(Money::fromCents(100), Money::fromCents(25)),
            'Purchase refused: not enough money (price 1.00, inserted 0.25)',
        ];
        yield 'sold out' => [EmptySlot::of(Selector::of('WATER')), 'Purchase refused: WATER is sold out'];
        yield 'no change' => [
            ChangeNotAvailable::for(Money::fromCents(35)),
            'Purchase refused: the change cannot be paid',
        ];
        yield 'an unknown slot' => [UnknownSlot::of(Selector::of('COFFEE')), 'Invalid input: GET-COFFEE'];
    }

    #[Test]
    #[DataProvider('refusals')]
    public function explains_each_refusal(Refusal $refusal, string $explanation): void
    {
        self::assertSame($explanation, RefusalView::for($refusal, 'GET-COFFEE'));
    }

    #[Test]
    public function fails_loudly_for_a_refusal_it_cannot_explain(): void
    {
        $this->expectException(LogicException::class);

        RefusalView::for(new class ('new') extends Refusal {}, 'GET-WATER');
    }
}
