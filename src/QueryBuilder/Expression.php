<?php
/**
 * Expression.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\QueryBuilder;

/**
 * Expression
 *
 * @package Dybasedev\Database\QueryBuilder
 */
class Expression extends Statement
{
    /**
     * @var string
     */
    protected $expression;

    /**
     * Expression constructor.
     *
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->expression;
    }
}