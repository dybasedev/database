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

        $statements[] = $this->compileSelect($structures['select']);

        if (isset($structures['from'])) {
            $statements[] = $this->compileFrom($structures['from']);
        }

        if (isset($structures['where'])) {
            $statements[] = $this->compileWhereConditions($structures['where']);
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
                case 'comparison':
                    list($column, $operator, $value) = $data;
                    $expression = sprintf(
                        '%s %s %s', $this->wrapReference($column), $operator, $this->wrapValue($value)
                    );

                    $conditions[] = ($booleanSymbol ? "{$boolean} " : '') . $expression;
                    $booleanSymbol = true;
                    break;
                case 'nested-o':
                    $conditions[] = ($booleanSymbol ? "{$boolean} " : '') . '(';
                    $booleanSymbol = false;
                    break;
                case 'nested-c':
                    $conditions[] = ')';
                    $booleanSymbol = true;
                    break;
                case 'boolean':
                    list($column, $symbol) = $data;
                    $conditions[] = ($booleanSymbol ? "{$boolean} " : '') . $this->wrapReference($column) . ' ' . $symbol;
                    $booleanSymbol = true;
                    break;
            }
        }

        return 'where ' . implode(' ', $conditions);
    }
}