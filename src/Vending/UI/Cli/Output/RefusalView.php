<?php

declare(strict_types=1);

namespace Vending\UI\Cli\Output;

use LogicException;
use Shared\Domain\Refusal;
use Vending\Domain\Exception\ChangeNotAvailable;
use Vending\Domain\Exception\EmptySlot;
use Vending\Domain\Exception\NotEnoughMoney;
use Vending\Domain\Exception\UnknownSlot;

final class RefusalView
{
    public static function for(Refusal $refusal, string $action): string
    {
        return match (true) {
            $refusal instanceof NotEnoughMoney => sprintf(
                'Purchase refused: not enough money (price %s, inserted %s)',
                MoneyFormat::of($refusal->price),
                MoneyFormat::of($refusal->inserted),
            ),
            $refusal instanceof EmptySlot => sprintf('Purchase refused: %s is sold out', $refusal->selector->value),
            $refusal instanceof ChangeNotAvailable => 'Purchase refused: the change cannot be paid',
            $refusal instanceof UnknownSlot => sprintf('Invalid input: %s', $action),
            default => throw new LogicException(sprintf('No explanation for the refusal %s.', $refusal::class)),
        };
    }
}
