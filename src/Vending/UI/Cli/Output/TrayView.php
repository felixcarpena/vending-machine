<?php

declare(strict_types=1);

namespace Vending\UI\Cli\Output;

use Vending\Application\GetOutputTray\OutputTrayContents;

final class TrayView
{
    public static function of(OutputTrayContents $tray): ?string
    {
        $items = array_map(CoinFormat::of(...), $tray->coins->largestFirst());

        return $items === [] ? null : sprintf('-> %s', implode(', ', $items));
    }
}
