<?php
/**
 * Date: 9/21/18
 * Time: 1:10 AM
 */

namespace QueryFilterSerializer\Config;

/**
 * Class StringEncoderOptions contains options for UrlQueryEncoder
 * @package QueryFilterSerializer\Config
 */
class UrlQueryEncoderOptions
{
    /**
     * Name of query filter name in query
     * @var string
     */
    public $filterName = '_';

    /**
     * Max depth for filter values. E.g. _filter[a][b][c][d][]=555 - this is depth of 4 (b.c.d.INDEX = 4)
     * If set to null, then depth won't be checked and any depth is allowed
     * @var integer|null
     */
    public $arrayMaxDepth = 1;
}