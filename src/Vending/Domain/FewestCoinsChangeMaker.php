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
}
