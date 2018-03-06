<?php
/**
 * Select.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\QueryBuilder\Manipulation;


use Dybasedev\Database\QueryBuilder\Grammars\Grammar;
use Dybasedev\Database\QueryBuilder\Grammars\MySQL;
use Dybasedev\Database\QueryBuilder\Statement;
use Dybasedev\Database\QueryBuilder\Traits\WhereConditions;

class Select extends Statement
{
    use WhereConditions;

    /**
     * Select constructor.
     *
     * @param $select
     */
    public function __construct($select)
    {
        $this->structures['select'] = $select;
    }

    public function from($references)
    {
        $this->structures['from'] = $references;
        
        return $this;
    }

    public function groupBy()
    {
        return $this;
    }

    public function orderBy()
    {
        return $this;
    }

    public function having()
    {
        return $this;
    }

    public function toString($grammar = MySQL::class)
    {
        if ($grammar instanceof Grammar) {
            return $grammar->compile($this);
        }
        
        return $this->toString(new $grammar);
    }
}