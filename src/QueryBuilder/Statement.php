<?php
/**
 * Statement.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\QueryBuilder;

/**
 * Statement
 *
 * @package Dybasedev\Database\QueryBuilder
 */
abstract class Statement
{
    /**
     * @var array
     */
    protected $structures = [];

    /**
     * @return array
     */
    public function getStructures()
    {
        return $this->structures;
    }

    /**
     * @return string
     */
    abstract public function toString();

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}