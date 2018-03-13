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
    const STRUCTURE_SELECT = 'select';
    const STRUCTURE_FROM   = 'from';
    const STRUCTURE_WHERE  = 'where';

    const SUB_TYPE_COMPARISON   = 'comparison';
    const SUB_TYPE_NESTED_OPEN  = 'nested-o';
    const SUB_TYPE_NESTED_CLOSE = 'nested-c';
    const SUB_TYPE_BOOLEAN      = 'boolean';
    const SUB_TYPE_PREDICATE    = 'predicate';

    abstract public function compile(Statement $statement);
}