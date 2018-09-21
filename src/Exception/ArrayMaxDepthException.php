<?php
/**
 * Date: 25.05.14
 * Time: 21:11
 */

namespace QueryFilterSerializer\Exception;

/**
 * Class ArrayMaxDepthException
 * @package QueryFilterSerializer\Exception
 */
class ArrayMaxDepthException extends ParsingException
{
    protected $message = 'Array max depth exceeded';
}