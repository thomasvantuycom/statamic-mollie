<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | The Mollie API key used to authenticate API requests. Use the Test API
    | key during development and switch to the Live API key when processing
    | real payments.
    |
    | https://docs.mollie.com/reference/authentication#api-keys
    |
    */

    'key' => env('MOLLIE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Currencies
    |--------------------------------------------------------------------------
    |
    | Define which currencies are available when configuring payments. Use ISO
    | 4217 currency codes (e.g. EUR, USD, GBP) supported by Mollie.
    |
    | https://docs.mollie.com/docs/multicurrency
    |
    */

    'currencies' => [
        'AED',
        'AUD',
        'BRL',
        'CAD',
        'CHF',
        'CZK',
        'DKK',
        'EUR',
        'GBP',
        'HKD',
        'HUF',
        'ILS',
        'ISK',
        'JPY',
        'MXN',
        'MYR',
        'NOK',
        'NZD',
        'PHP',
        'PLN',
        'RON',
        'RUB',
        'SEK',
        'SGD',
        'THB',
        'TWD',
        'USD',
        'ZAR',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default currency used when none is explicitly selected. Must be one
    | of the currencies defined above.
    |
    */

    'default_currency' => 'EUR',

];
