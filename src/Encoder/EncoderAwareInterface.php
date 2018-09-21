<?php
/**
 * Date: 9/21/18
 * Time: 11:11 PM
 */

namespace QueryFilterSerializer\Encoder;


interface EncoderAwareInterface
{
    /**
     * @param EncoderInterface|null $encoder
     * @return mixed
     */
    public function setEncoder(EncoderInterface $encoder = null);

    /**
     * @return EncoderInterface|null
     */
    public function getEncoder();
}