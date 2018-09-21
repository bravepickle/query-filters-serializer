<?php
/**
 * Date: 9/21/18
 * Time: 1:10 AM
 */

namespace QueryFilterSerializer\Config;

/**
 * Class QueryEncoderOptions contains options for QueryEncoder
 * @package QueryFilterSerializer\Filter
 */
class QueryEncoderOptions
{
    /**
     * @var string value-name delimiter
     */
    public $nameValueDelimiter = ':';

    /**
     * @var string constraint delimiter between each filter
     */
    public $constraintDelimiter = '|';

    /**
     * Escape character
     * @var string
     */
    public $escapeStr = '\\';
}