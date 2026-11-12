<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\UI\Cli\Input;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\UI\Cli\Input\ActionParser;

final class ActionParserTest extends TestCase
{
    #[Test]
    public function splits_a_line_into_its_actions(): void
    {
        $actions = ActionParser::actionsIn("0.25, 1, GET-WATER\n");

        self::assertSame(['0.25', '1', 'GET-WATER'], $actions);
    }

    #[Test]
    public function skips_empty_actions(): void
    {
        $actions = ActionParser::actionsIn(' , 1,, ');

        self::assertSame(['1'], $actions);
    }

    #[Test]
    #[DataProvider('exitLines')]
    public function recognises_the_exit_line(string $line): void
    {
        self::assertTrue(ActionParser::isExit($line));
    }

    /** @return array<string, array{string}> */
    public static function exitLines(): array
    {
        return [
            'alone' => ['EXIT'],
            'with a line break' => ["EXIT\n"],
            'with spaces' => ['  EXIT  '],
        ];
    }

    #[Test]
    public function does_not_exit_when_exit_comes_with_other_actions(): void
    {
        self::assertFalse(ActionParser::isExit('1, EXIT'));
    }
}
