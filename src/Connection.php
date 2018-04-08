<?php
/**
 * Connection.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database;


abstract class Connection
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Connection constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    abstract public function connect();

    /**
     * @return mixed
     */
    abstract public function disconnect();

    /**
     * @return mixed
     */
    abstract public function reconnect();
}