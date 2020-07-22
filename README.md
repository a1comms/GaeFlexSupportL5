# GaeSupportLaravel

Google App Engine (GAE) Standard Environment support package for **Laravel 6.0 LTS**.

[![Latest Stable Version](https://poser.pugx.org/a1comms/gae-support-laravel/v/stable)](https://packagist.org/packages/a1comms/gae-support-laravel)
[![Monthly Downloads](https://poser.pugx.org/a1comms/gae-support-laravel/d/monthly)](https://packagist.org/packages/a1comms/gae-support-laravel)
[![Total Downloads](https://poser.pugx.org/a1comms/gae-support-laravel/downloads)](https://packagist.org/packages/a1comms/gae-support-laravel)
[![Latest Unstable Version](https://poser.pugx.org/a1comms/gae-support-laravel/v/unstable)](https://packagist.org/packages/a1comms/gae-support-laravel)
[![License](https://poser.pugx.org/a1comms/gae-support-laravel/license)](https://packagist.org/packages/a1comms/gae-support-laravel)

Based on original work for App Engine Standard (on the PHP5.5 runtime) by @shpasser https://github.com/shpasser/GaeSupportL5

This library is designed for homogeneous operation between the Standard Environment and the Flexible Environment.

*Note: we only intend to support Laravel LTS releases, with this version targeted specifically at **Laravel 6.0 LTS***

## Functionality
* StackDriver Logging integration
* StackDriver Trace integration (see [docs/trace.md](https://github.com/a1comms/GaeSupportLaravel/blob/php7.4-laravel6.0/docs/trace.md))
* Blade View Pre-Compiler (optional, see [docs/blade-pre-compile.md](https://github.com/a1comms/GaeSupportLaravel/blob/php7.4-laravel6.0/docs/blade-pre-compile.md))
* Guzzle integration (optional, see [docs/trace.md](https://github.com/a1comms/GaeSupportLaravel/blob/php7.4-laravel6.0/docs/trace.md#guzzle))
* Laravel Auth Integration for IAP (optional, see [docs/iap-auth-verify.md](https://github.com/a1comms/GaeSupportLaravel/blob/php7.4-laravel6.0/docs/iap-auth-verify.md))
* Queue Driver for Cloud Tasks (optional, see [docs/queue.md](https://github.com/a1comms/GaeSupportLaravel/blob/php7.4-laravel6.0/docs/queue.md))
* Examples for deployment from Git via Cloud Build, plus encrypted secrets with KMS (optional, see [docs/cloudbuild.md](https://github.com/a1comms/GaeSupportLaravel/blob/php7.4-laravel6.0/docs/cloudbuild.md))

## Installation

Pull in the package via Composer:

```js
"require": {
    "a1comms/gae-support-laravel": "~6.0"
}
```

### Laravel Specific (Not Lumen)

**1.** Add the following to `composer.json`:

```json
    "scripts": {
        "post-autoload-dump": [
            "php artisan gae:prepare",
            "php artisan view:cache"
        ]
    },
```

**2.** For Laravel, include the service provider within `config/app.php`:

```php
    'providers' => [
        A1comms\GaeSupportLaravel\GaeSupportServiceProvider::class,
    ];
```

**3.** Also, for added functionality, include the optional service providers:

```php
    'providers' => [
        A1comms\GaeSupportLaravel\Auth\AuthServiceProvider::class,
        A1comms\GaeSupportLaravel\Queue\QueueServiceProvider::class,
        A1comms\GaeSupportLaravel\Trace\TraceServiceProvider::class,
    ];
```

And remove the relevant Laravel service providers that these replace:

```php
    'providers' => [
        //Illuminate\Queue\QueueServiceProvider::class,
    ];
```

**4.** Update `bootstrap/app.php` to load the overridden application class & initialise logging to Stackdriver:

```php
/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new A1comms\GaeSupportLaravel\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);
```

**5.** Update `app/Exceptions/Handler.php` to enable proper Exception logging to StackDriver Error Reporting & Logging:

Change the following `use` statement:

```php
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
```

To our class, that'll inject the required logging hook:

```php
use A1comms\GaeSupportLaravel\Foundation\Exceptions\Handler as ExceptionHandler;
```

**6.** In `config/logging.php`, configure a custom logger:

```php
'gae' => [
    'driver' => 'custom',
    'via' => A1comms\GaeSupportLaravel\Log\CreateLoggingDriver::class,
],
```

**7.** In `.env`, set the following:

```
QUEUE_CONNECTION=gae
CACHE_DRIVER=array
SESSION_DRIVER=gae
LOG_CHANNEL=gae
```

### Lumen Specific (Not Laravel)

**1.** Update `bootstrap/app.php` to load the overridden application class

```php
/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new A1comms\GaeSupportLaravel\Foundation\LumenApplication(
    realpath(__DIR__.'/../')
);
```

**2.** Update `app/Exceptions/Handler.php` to enable proper Exception logging to StackDriver Error Reporting & Logging:

Change the following `use` statement:

```php
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
```

To our class, that'll inject the required logging hook:

```php
use A1comms\GaeSupportLaravel\Foundation\Exceptions\LumenHandler as ExceptionHandler;
```

## Upgrading (from Laravel 5.5 LTS on `a1comms/gae-support-laravel:~5.5`)

### Laravel Specific (Not Lumen)

**1.** Remove the following from `bootstrap/app.php`:

```php
/*
|--------------------------------------------------------------------------
| Setup Early Logging
|--------------------------------------------------------------------------
*/
A1comms\GaeSupportLaravel\Log\Logger::setup($app);
```

**2.** In `config/logging.php`, configure a custom logger:

```php
'gae' => [
    'driver' => 'custom',
    'via' => A1comms\GaeSupportLaravel\Log\CreateLoggingDriver::class,
],
```