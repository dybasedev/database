<?php
/**
 * ConnectionManager.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\SQL;

use Dybasedev\Database\ConnectionManager as BaseConnectionManager;
use Dybasedev\Database\Exceptions\DriverNotSupportException;
use Dybasedev\Database\SQL\Connections\MySQLConnection;

/**
 * Class ConnectionManager
 *
 * @method Connection connection($name = null)
 *
 * @package Dybasedev\Database\SQL
 */
class ConnectionManager extends BaseConnectionManager
{
    /**
     * @param string $name
     *
     * @return \Dybasedev\Database\Connection|Connection|MySQLConnection
     */
    public function createConnection($name)
    {
        switch ($this->config['connections'][$name]['driver']) {
            case 'mysql':
                return new MySQLConnection($this->config['connections'][$name]);
            default:
                if ($this->container && $this->container->bound($abstract = 'db.sql.driver:' . $name)) {
                    return $this->container->make($abstract);
                }

                throw new DriverNotSupportException($name);
        }
    }

}