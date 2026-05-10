<?php

namespace ThomasVantuycom\StatamicMollie\Enums;

enum Currency: string
{
    case AED = 'AED';
    case AUD = 'AUD';
    case BRL = 'BRL';
    case CAD = 'CAD';
    case CHF = 'CHF';
    case CZK = 'CZK';
    case DKK = 'DKK';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case HKD = 'HKD';
    case HUF = 'HUF';
    case ILS = 'ILS';
    case ISK = 'ISK';
    case JPY = 'JPY';
    case MXN = 'MXN';
    case MYR = 'MYR';
    case NOK = 'NOK';
    case NZD = 'NZD';
    case PHP = 'PHP';
    case PLN = 'PLN';
    case RON = 'RON';
    case RUB = 'RUB';
    case SEK = 'SEK';
    case SGD = 'SGD';
    case THB = 'THB';
    case TWD = 'TWD';
    case USD = 'USD';
    case ZAR = 'ZAR';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function decimals(): int
    {
        return match ($this) {
            self::ISK,
            self::JPY => 0,

            self::AED,
            self::AUD,
            self::BRL,
            self::CAD,
            self::CHF,
            self::CZK,
            self::DKK,
            self::EUR,
            self::GBP,
            self::HKD,
            self::HUF,
            self::ILS,
            self::MXN,
            self::MYR,
            self::NOK,
            self::NZD,
            self::PHP,
            self::PLN,
            self::RON,
            self::RUB,
            self::SEK,
            self::SGD,
            self::THB,
            self::TWD,
            self::USD,
            self::ZAR => 2,

            default => 2,
        };
    }
}
