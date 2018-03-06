<?php
/**
 * QueryBuilderTest.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Test\Database\QueryBuilder;

use Dybasedev\Database\QueryBuilder\Manipulation\Select;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{

    public function testMySQLSelectStatement()
    {
        $r = (new Select(['id', 'name', 'password']))->from('foo')->where('foo', '=', 1)->where('bar', '=', 2)->orWhere('baz', '!=', 3)->whereNested(function (Select $select) {
            $select->whereIsNotNull('qux')->orWhereNested(function (Select $select) {
                $select->whereIsNull('das')->where('pis', '>', 213);
            })->orWhere('jar', '<=', 8);
        })->toString();

        print $r;
        
        $this->assertEquals('select `id`, `name`, `password` from `foo` where `foo` = ? and `bar` = ? or `baz` != ? and ( `qux` is not null or ( `das` is null and `pis` > ? ) or `jar` <= ? )', $r);
    }
}
