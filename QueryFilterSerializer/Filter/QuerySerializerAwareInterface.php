<?php
/**
 * User: vkon
 * Date: 1/29/15
 * Time: 18:18
 */

namespace QueryFilterSerializer\Filter;


interface QuerySerializerAwareInterface
{
    /**
     * @param QuerySerializer $serializer
     */
    public function setSerializer(QuerySerializer $serializer = null);
}