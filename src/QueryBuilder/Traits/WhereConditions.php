<?php
/**
 * WhereConditions.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\QueryBuilder\Traits;


use Closure;

trait WhereConditions
{
    public function where($column, $operator, $value, $boolean = 'and')
    {
        $this->structures['where'][] = ['comparison', [$column, $operator, $value], $boolean];

        return $this;
    }

    public function orWhere($column, $operator, $value)
    {
        return $this->where($column, $operator, $value, 'or');
    }

    public function whereNested(Closure $callback, $boolean = 'and')
    {
        $this->structures['where'][] = ['nested-o', null, $boolean];

        ($callback)($this);

        $this->structures['where'][] = ['nested-c', null, null];

        return $this;
    }

    public function orWhereNested(Closure $callback)
    {
        return $this->whereNested($callback, 'or');
    }

    public function whereIsNull($column, $isNull = true, $boolean = 'and')
    {
        $symbol = $isNull ? 'is null' : 'is not null';

        $this->structures['where'][] = ['boolean', [$column, $symbol], $boolean];

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

        $this->structures['where'][] = ['predicate', [$column, $symbol, $values], $boolean];

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