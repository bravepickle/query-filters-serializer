<?php

namespace QueryFilterSerializer\Filter\Type;


use QueryFilterSerializer\Exception\ArrayMaxDepthException;
use QueryFilterSerializer\Filter\QueryFilterTypeInterface;

abstract class AbstractType implements QueryFilterTypeInterface
{
    const NAME = 'UNKNOWN';
    const MAX_DEPTH = 0;

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

    /**
     * @param mixed $data
     * @param $maxDepth
     * @throws ArrayMaxDepthException
     */
    protected function assertArrayMaxDepth($data, $maxDepth)
    {
        if (!is_array($data) || $maxDepth === null) {
            return;
        }

        if ($maxDepth === 0) {
            throw new ArrayMaxDepthException('Array max depth is exceeded for filter ' . $this->getName());
        }

        --$maxDepth;
        foreach ($data as $datum) {
            if (is_array($datum)) {
                if ($maxDepth === 0) {
                    throw new ArrayMaxDepthException('Array max depth is exceeded for filter ' . $this->getName());
                }

                $this->assertArrayMaxDepth($data, $maxDepth);
            }
        }
    }

    /**
     * @param $data
     * @throws ArrayMaxDepthException
     */
    protected function checkArrayDepth($data)
    {
        $this->assertArrayMaxDepth($data, static::MAX_DEPTH);
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