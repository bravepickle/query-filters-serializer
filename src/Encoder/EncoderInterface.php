<?php
/**
 * User: user
 * Date: 21.09.18
 * Time: 14:57
 */

namespace QueryFilterSerializer\Encoder;

/**
 * Interface EncoderInterface encodes and decodes filters query data
 * @package QueryFilterSerializer\Filter
 */
interface EncoderInterface
{
    const CONTEXT_CONSTRAINTS = 'constraints';
    const CONTEXT_ENCODING = 'encoding';
    const CONTEXT_SERIALIZER = 'serializer';

    /**
     * @param mixed $data
     * @param array $context
     * @return array
     */
    public function decode($data, $context = []);

    /**
     * @param array $data
     * @param array $context
     * @return string
     */
    public function encode($data, $context = []);
}