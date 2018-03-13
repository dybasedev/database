<?php
/**
 * WhereConditions.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\QueryBuilder\Traits;


use Closure;
use Dybasedev\Database\QueryBuilder\Grammars\Grammar;

trait WhereConditions
{
    public function where($column, $operator, $value, $boolean = 'and')
    {
        $this->structures[Grammar::STRUCTURE_WHERE][] =
            [Grammar::SUB_TYPE_COMPARISON, [$column, $operator, $value], $boolean];

        return $this;
    }

    public function orWhere($column, $operator, $value)
    {
        return $this->where($column, $operator, $value, 'or');
    }

    public function whereNested(Closure $callback, $boolean = 'and')
    {
        $this->structures[Grammar::STRUCTURE_WHERE][] = [Grammar::SUB_TYPE_NESTED_OPEN, null, $boolean];

        ($callback)($this);

        $this->structures[Grammar::STRUCTURE_WHERE][] = [Grammar::SUB_TYPE_NESTED_CLOSE, null, null];

        return $this;
    }

    public function orWhereNested(Closure $callback)
    {
        return $this->whereNested($callback, 'or');
    }

    public function whereIsNull($column, $isNull = true, $boolean = 'and')
    {
        $symbol = $isNull ? 'is null' : 'is not null';

        $this->structures[Grammar::STRUCTURE_WHERE][] = [Grammar::SUB_TYPE_BOOLEAN, [$column, $symbol], $boolean];

        return $this;
    }

    public function whereIsNotNull($column, $boolean = 'and')
    {
        return $this->whereIsNull($column, false, $boolean);
    }

    public function orWhereIsNull($column, $isNull = true)
    {
        return $this->whereIsNull($column, $isNull, 'or');
    }

    public function orWhereIsNotNull($column)
    {
        return $this->orWhereIsNull($column, false);
    }

    public function whereIn($column, $values, $not = false, $boolean = 'and')
    {
        $symbol = $not ? 'not in' : 'in';

        $this->structures[Grammar::STRUCTURE_WHERE][] = [
            Grammar::SUB_TYPE_PREDICATE, [$column, $symbol, $values], $boolean,
        ];

        return $this;
    }

    public function orWhereIn($column, $values, $not = false)
    {
        return $this->whereIn($column, $values, $not, 'or');
    }

    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, true, $boolean);
    }

    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'or');
    }
}