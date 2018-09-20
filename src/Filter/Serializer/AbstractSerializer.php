<?php

namespace QueryFilterSerializer\Filter\Serializer;


abstract class AbstractSerializer
{
    protected $options = array();

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * Get serializer type field
     * @return string
     */
    public function getName()
    {
        return static::NAME; // late static binding. At last i'm using it! Make sure to define "const NAME" in children
    }

    abstract public function serialize(array $data);
    abstract public function unserialize($data);

    /**
     * Create piece of SQL with placeholder and values
     * @param $data
     * @param $tableAlias
     * @return array first element is string, the second - list of values
     */
    abstract public function buildSqlParts($data, $tableAlias = 't');
}