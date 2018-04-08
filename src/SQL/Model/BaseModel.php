<?php
/**
 * BaseModel.php
 *
 * @copyright Chongyi <chongyi@xopns.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Database\SQL\Model;

/**
 * 基础模型
 *
 * @package Dybasedev\Database\SQL\Model
 */
abstract class BaseModel
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $attributeSetters = [];

    /**
     * @var array
     */
    protected $attributeGetters = [];

    /**
     * @var array
     */
    protected $nativeAttributes = [];

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->attributeGetters[$name])) {
            return ($this->attributeGetters[$name])($this->attributes[$name]);
        }
        return $this->attributes[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (isset($this->attributeSetters[$name])) {
            ($this->attributeSetters[$name])($value);
        } else {
            $this->attributes[$name] = $value;
        }
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->attributes[$name]);
    }

    /**
     * @param bool $native
     *
     * @return array
     */
    public function getAttributes($native = true)
    {
        if ($native && $this->nativeAttributes) {
            $attributes = [];
            foreach ($this->nativeAttributes as $attribute) {
                $attributes[$attribute] = $this->{$attribute};
            }

            return $this->attributes + $attributes;
        }

        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
}