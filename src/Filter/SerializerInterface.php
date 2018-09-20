<?php
/**
 * Date: 9/21/18
 * Time: 12:37 AM
 */

namespace QueryFilterSerializer\Filter;


interface SerializerInterface
{
    /**
     * Serialize data
     * @param array $data
     * @return mixed
     */
    public function serialize(array $data);

    /**
     * Unserialize data
     * @param $data
     * @return mixed
     */
    public function unserialize($data);
}