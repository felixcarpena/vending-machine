<?php

declare(strict_types=1);

namespace Vending\UI\Cli\Input;

final class ActionParser
{
    public const string EXIT = 'EXIT';

    public static function isExit(string $line): bool
    {
        return trim($line) === self::EXIT;
    }

    /** @return list<string> */
    public static function actionsIn(string $line): array
    {
        $actions = array_map(trim(...), explode(',', $line));

        return array_values(array_filter($actions, static fn(string $action): bool => $action !== ''));
    }
}
