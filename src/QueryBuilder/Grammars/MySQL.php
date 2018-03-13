<?php
/**
 * MySQL.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\QueryBuilder\Grammars;


use Dybasedev\Database\QueryBuilder\Expression;
use Dybasedev\Database\QueryBuilder\Manipulation\Select;
use Dybasedev\Database\QueryBuilder\Placeholder;
use Dybasedev\Database\QueryBuilder\Statement;

class MySQL extends Grammar
{
    public function compile(Statement $statement)
    {
        if ($statement instanceof Select) {
            return $this->compileSelectStatement($statement);
        }
    }

    public function compileSelectStatement(Select $select)
    {
        $structures = $select->getStructures();

        $statements[] = $this->compileSelect($structures[Grammar::STRUCTURE_SELECT]);

        if (isset($structures[Grammar::STRUCTURE_FROM])) {
            $statements[] = $this->compileFrom($structures[Grammar::STRUCTURE_FROM]);
        }

        if (isset($structures[Grammar::STRUCTURE_WHERE])) {
            $statements[] = $this->compileWhereConditions($structures[Grammar::STRUCTURE_WHERE]);
        }

        return implode(' ', $statements);
    }

    public function compileSelect($structure)
    {
        if (is_array($structure)) {
            $selects = [];

            foreach ($structure as $item) {
                $selects[] = $this->wrapReference($item);
            }

            $result = implode(', ', $selects);
        } else {
            $result = $this->wrapReference($structure);
        }

        return 'select ' . $result;
    }

    public function compileFrom($structure)
    {
        $result = $this->wrapReference($structure);

        return 'from ' . $result;
    }

    public function wrapValue($expression)
    {
        if ($expression instanceof Expression) {
            return $expression->toString();
        }

        if ($expression instanceof Placeholder) {
            return $expression->mark();
        }

        return '?';
    }

    public function wrapReference($expression)
    {
        if ($expression instanceof Expression) {
            return $expression->toString();
        }

        return "`{$expression}`";
    }

    public function compileWhereConditions($structure)
    {
        $booleanSymbol = false;
        $conditions = [];
        foreach ($structure as list($type, $data, $boolean)) {
            switch ($type) {
                case Grammar::SUB_TYPE_COMPARISON:
                    list($column, $operator, $value) = $data;
                    $expression = sprintf(
                        '%s %s %s', $this->wrapReference($column), $operator, $this->wrapValue($value)
                    );

                    $conditions[] = ($booleanSymbol ? "{$boolean} " : '') . $expression;
                    $booleanSymbol = true;
                    break;
                case Grammar::SUB_TYPE_NESTED_OPEN:
                    $conditions[] = ($booleanSymbol ? "{$boolean} " : '') . '(';
                    $booleanSymbol = false;
                    break;
                case Grammar::SUB_TYPE_NESTED_CLOSE:
                    $conditions[] = ')';
                    $booleanSymbol = true;
                    break;
                case Grammar::SUB_TYPE_BOOLEAN:
                    list($column, $symbol) = $data;
                    $conditions[] = ($booleanSymbol ? "{$boolean} " : '') . $this->wrapReference($column) . ' ' . $symbol;
                    $booleanSymbol = true;
                    break;
            }
        }

        return 'where ' . implode(' ', $conditions);
    }
}