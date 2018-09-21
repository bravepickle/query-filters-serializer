<?php
/**
 * Date: 9/21/18
 * Time: 1:10 AM
 */

namespace QueryFilterSerializer\Config;

/**
 * Class StringEncoderOptions contains options for StringEncoder
 * @package QueryFilterSerializer\Config
 */
class StringEncoderOptions
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