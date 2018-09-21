<?php
/**
 * Date: 9/21/18
 * Time: 10:06 PM
 */

namespace QueryFilterSerializer\Encoder\Filter;


use QueryFilterSerializer\Encoder\EncoderInterface;

/**
 * Class ArrayEmbeddedTypeEncoder encoder for parsing arrays
 * @package QueryFilterSerializer\Encoder\Filter
 */
class ArrayEmbeddedTypeEncoder implements EncoderInterface
{
    public function decode($data, $context = [])
    {
        return $data; // leave as it is
    }

    public function encode($data, $context = [])
    {
        // TODO: Implement encode() method.
    }

}