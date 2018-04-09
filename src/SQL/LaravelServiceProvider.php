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
        $this->app->singleton(ConnectionManager::class, function (Container $container) {
            $manager = new ConnectionManager($container->make(Repository::class)->get('database'));
            $manager->setContainer($container);

            return $manager;
        });

        $this->app->bind(Connection::class, function (Container $container) {
            /** @var ConnectionManager $manager */
            $manager = $container->make(ConnectionManager::class);

            return $manager->connection();
        });
    }
}