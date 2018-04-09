<?php
/**
 * ConnectionManager.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database;

use Illuminate\Contracts\Container\Container;

abstract class ConnectionManager
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var array|Connection[]
     */
    protected $connections;

    /**
     * @var Container
     */
    protected $container;

    /**
     * ConnectionManager constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param Container $container
     *
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    public function connection($name = null)
    {
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        $this->connections[$name] = $this->createConnection($name);
        $this->connections[$name]->connect();

        return $this->connections[$name];
    }

    /**
     * @param string $name
     *
     * @return Connection
     */
    abstract public function createConnection($name);


    public function getDefaultConnection()
    {
        return $this->config['default'];
    }
}