<?php

declare(strict_types=1);

namespace Vending\Domain;

final readonly class FewestCoinsChangeMaker implements ChangeMaker
{
    public function changeFor(Money $amount, Coins $available): ?Coins
    {
        $fewestCoinsFor = [0 => []];
        foreach ($available->largestFirst() as $coin) {
            if (!$coin->canBeGivenAsChange()) {
                continue;
            }

            for ($cents = $amount->cents; $cents >= $coin->value; --$cents) {
                $withoutThisCoin = $fewestCoinsFor[$cents - $coin->value] ?? null;
                $best = $fewestCoinsFor[$cents] ?? null;
                if ($withoutThisCoin !== null && ($best === null || count($withoutThisCoin) + 1 < count($best))) {
                    $fewestCoinsFor[$cents] = [...$withoutThisCoin, $coin];
                }
            }
        }

        $fewest = $fewestCoinsFor[$amount->cents] ?? null;

        return $fewest === null ? null : Coins::of(...$fewest);
    }

    public function payableUpTo(Money $limit, Coins $available): PayableChange
    {
        $reachable = [0 => true];
        foreach (Coin::cases() as $coin) {
            $count = $available->countOf($coin);
            if (!$coin->canBeGivenAsChange() || $count === 0) {
                continue;
            }

            $usedFor = [];
            for ($cents = $coin->value; $cents <= $limit->cents; ++$cents) {
                $without = $cents - $coin->value;
                $used = $usedFor[$without] ?? 0;
                if (!isset($reachable[$cents]) && isset($reachable[$without]) && $used < $count) {
                    $reachable[$cents] = true;
                    $usedFor[$cents] = $used + 1;
                }
            }
        }

        return PayableChange::of(...array_map(Money::fromCents(...), array_keys($reachable)));
    }
}
