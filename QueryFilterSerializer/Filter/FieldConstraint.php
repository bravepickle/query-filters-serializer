<?php

namespace QueryFilterSerializer\Filter;


class FieldConstraint implements \ArrayAccess, \JsonSerializable
{
    protected $data = [
        'condition' => null,
        'value' => null,
    ];

    function __construct(array $data)
    {
        $this->data = $data;
    }

    public function setCondition($condition)
    {
        $this->data['condition'] = $condition;
    }

    public function getCondition()
    {
        return $this->data['condition'];
    }

    public function setValues($values)
    {
        $this->data['value'] = $values;
    }

    public function getValues()
    {
        return $this->data['value'];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return $this->data;
    }
}
