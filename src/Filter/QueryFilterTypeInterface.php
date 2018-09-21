<?php
/**
 * Date: 9/21/18
 * Time: 12:32 AM
 */

namespace QueryFilterSerializer\Filter;

use QueryFilterSerializer\Serializer\SerializerInterface;

/**
 * Interface QueryFilterTypeInterface
 * Contains basic functionality for using
 * @package QueryFilterSerializer\Filter\Type
 */
interface QueryFilterTypeInterface extends SerializerInterface
{
    /**
     * @param $options
     * @return $this
     */
    public function setOptions($options);

    /**
     * @return mixed
     */
    public function getOptions();

    /**
     * @param $name
     * @param $default
     * @return mixed
     */
    public function getOption($name, $default = null);

    /**
     * Get serializer type field
     * @return string
     */
    public function getName();

    /**
     * Create piece of SQL with placeholder and values
     * @param $data
     * @param string $tableAlias
     * @return array first element is string, the second - list of values
     */
    public function buildSqlParts($data, $tableAlias);
}