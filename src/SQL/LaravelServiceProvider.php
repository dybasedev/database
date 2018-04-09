<?php
/**
 * LaravelServiceProvider.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\SQL;


use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class LaravelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ConnectionManager::class, function (Container $app) {
            return new ConnectionManager($app->make(Repository::class)->get('database'));
        });
    }
}