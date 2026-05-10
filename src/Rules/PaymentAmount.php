<?php

namespace ThomasVantuycom\StatamicMollie\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ThomasVantuycom\StatamicMollie\Enums\Currency;
use ThomasVantuycom\StatamicMollie\Support\Currencies;

class PaymentAmount implements ValidationRule
{
    public static function containsAntlers(string $value): bool
    {
        return str_contains($value, '{{') && str_contains($value, '}}');
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('mollie::validation.amount')->translate();

            return;
        }

        $currency = $value['currency'] ?? null;
        $amount = $value['value'] ?? null;

        if (! is_string($currency) || ! in_array($currency, Currencies::enabled(), true)) {
            $fail('mollie::validation.amount_currency')->translate();

            return;
        }

        if (! is_string($amount)) {
            $fail('mollie::validation.amount_value')->translate();

            return;
        }

        $amount = trim($amount);

        if (self::containsAntlers($amount)) {
            return;
        }

        if (! is_numeric($amount)) {
            $fail('mollie::validation.amount_value')->translate();

            return;
        }

        $currency = Currency::from($currency);
        $decimals = $currency->decimals();

        $pattern = $decimals === 0
            ? '/^\d+$/'
            : '/^\d+\.\d{'.$decimals.'}$/';

        if (! preg_match($pattern, $amount)) {
            $fail('mollie::validation.amount_value_decimals')->translate([
                'decimals' => $decimals,
            ]);

            return;
        }

        if ((float) $amount <= 0) {
            $fail('mollie::validation.amount_value_positive')->translate();
        }
    }
}
