<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| FrankenPHP SAPI fix (ponytail)
|--------------------------------------------------------------------------
| FrankenPHP runs under the cli SAPI, so Laravel's runningInConsole() returns
| true even for HTTP requests. This prevents hyn/multi-tenant from identifying
| the tenant hostname and loading tenant routes. Set the env var before Laravel
| boots so Application::runningInConsole() picks it up. Only affects HTTP requests
| (artisan doesn't go through index.php).
*/
if (!getenv('APP_RUNNING_IN_CONSOLE')) {
    putenv('APP_RUNNING_IN_CONSOLE=false');
    $_ENV['APP_RUNNING_IN_CONSOLE'] = 'false';
    $_SERVER['APP_RUNNING_IN_CONSOLE'] = 'false';
}

/*
|--------------------------------------------------------------------------
| FrankenPHP multi-app env isolation fix (ponytail)
|--------------------------------------------------------------------------
| FrankenPHP shares the PHP process across all Caddy sites. Dotenv's default
| immutable repository won't overwrite env vars set by a previous request to
| a different app (e.g. DB_DATABASE from despacho leaking into pro8). Force-set
| all .env vars here so this app's env is authoritative on every request.
*/
if (is_file(__DIR__ . '/../.env')) {
    foreach (file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        if ($v !== '' && $v[0] !== '$') {
            putenv("$k=$v");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
