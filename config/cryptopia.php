<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Binance authentication
    |--------------------------------------------------------------------------
    |
    | Authentication key and secret for Binance API.
    |
     */

    'auth' => [
        'key'    => env('CRYPTOPIA_KEY', ''),
        'secret' => env('CRYPTOPIA_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Api URLS
    |--------------------------------------------------------------------------
    |
    | Binance API endpoints
    |
     */

    'urls' => [
        'api'  => 'https://www.cryptopia.co.nz/api/'
    ],

];
