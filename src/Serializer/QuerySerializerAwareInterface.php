<?php
/**
 * Date: 1/29/15
 * Time: 18:18
 */

namespace QueryFilterSerializer\Serializer;


interface QuerySerializerAwareInterface
{
    /**
     * @param QuerySerializer $serializer
     */
    public function setSerializer(QuerySerializer $serializer = null);
}