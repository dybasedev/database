<?php
/**
 * KeeperDatabaseModuleProvider.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\SQL;


use Dybasedev\KeeperContracts\Module\ModuleProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;

class KeeperDatabaseModuleProvider implements ModuleProvider
{
    public function register(Container $container)
    {
        $container->singleton(ConnectionManager::class, function (Container $container) {
            return new ConnectionManager($container->make(Repository::class)->get('database'));
        });
    }

    public function mount(Container $container)
    {
        //
    }

}