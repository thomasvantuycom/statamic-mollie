<?php

namespace ThomasVantuycom\StatamicMollie\Support;

use ThomasVantuycom\StatamicMollie\Enums\Currency;

class Currencies
{
    public static function enabled(): array
    {
        return array_values(array_intersect(
            config('statamic.mollie.currencies', Currency::values()),
            Currency::values()
        ));
    }

    public static function default(): ?string
    {
        $default = config('statamic.mollie.default_currency', Currency::EUR->value);

        return in_array($default, self::enabled(), true)
            ? $default
            : null;
    }
}
