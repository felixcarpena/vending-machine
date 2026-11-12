<?php

declare(strict_types=1);

namespace Vending\UI\Cli\Input;

use Vending\Application\GetMachineContents\GetMachineContents;
use Vending\Application\ServiceMachine\ServiceMachine;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\StockLevels;
use Vending\Domain\VendingMachineId;

final class ActionParser
{
    public const string SERVICE = 'SERVICE';
    public const string STATUS = 'STATUS';
    public const string EXIT = 'EXIT';

    private const string SELECTOR = '/^[A-Z][A-Z0-9]*$/';

    public static function isExit(string $line): bool
    {
        return trim($line) === self::EXIT;
    }

    /** @return list<string>|InvalidLine */
    public static function actionsIn(string $line): array|InvalidLine
    {
        $actions = array_map(trim(...), explode(',', $line));
        $servicing = array_any($actions, static fn(string $action): bool => str_starts_with($action, self::SERVICE));
        if ($servicing && str_contains($line, ',')) {
            return new InvalidLine(trim($line));
        }

        return array_values(array_filter($actions, static fn(string $action): bool => $action !== ''));
    }

    public static function messageFor(
        string $action,
        VendingMachineId $machineId,
    ): ServiceMachine|GetMachineContents|null {
        return match (true) {
            $action === self::STATUS => new GetMachineContents($machineId),
            str_starts_with($action, self::SERVICE) => self::servicingIn($action, $machineId),
            default => null,
        };
    }

    private static function servicingIn(string $action, VendingMachineId $machineId): ?ServiceMachine
    {
        $words = preg_split('/\s+/', $action) ?: [];
        if (array_shift($words) !== self::SERVICE || $words === []) {
            return null;
        }

        $stock = [];
        $change = [];
        foreach ($words as $word) {
            if (preg_match('/^(?<key>[^=]+)=(?<count>\d+)$/', $word, $match) !== 1) {
                return null;
            }
            $count = (int) $match['count'];
            $coin = self::coinWrittenAs($match['key']);
            if ($coin !== null) {
                $change = [...$change, ...array_fill(0, $count, $coin)];
            } elseif (preg_match(self::SELECTOR, $match['key']) === 1) {
                $stock[$match['key']] = $count;
            } else {
                return null;
            }
        }

        return new ServiceMachine($machineId, StockLevels::of($stock), Coins::of(...$change));
    }

    private static function coinWrittenAs(string $written): ?Coin
    {
        return match ($written) {
            '0.05' => Coin::FiveCents,
            '0.10' => Coin::TenCents,
            '0.25' => Coin::TwentyFiveCents,
            '1' => Coin::OneHundredCents,
            default => null,
        };
    }
}
