# laravel-cryptopia
Laravel implementation of Cryptopia trading API

## Install

#### Install via Composer

```
composer require adman9000/laravel-cryptopia
```

Utilises autoloading in Laravel 5.5+. For older versions add the following lines to your `config/app.php`

```php
'providers' => [
        ...
        adman9000\cryptopia\CryptopiaServiceProvider::class,
        ...
    ],


 'aliases' => [
        ...
        'Cryptopia' => adman9000\cryptopia\CryptopiaAPIFacade::class,
    ],
```
