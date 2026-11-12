<?php

declare(strict_types=1);

namespace Vending\Domain;

interface ChangeMaker
{
    public function changeFor(Money $amount, Coins $available): ?Coins;
}
