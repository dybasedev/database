<?php
/**
 * Grammar.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\QueryBuilder\Grammars;


use Dybasedev\Database\QueryBuilder\Statement;

abstract class Grammar
{
    abstract public function compile(Statement $statement);
}