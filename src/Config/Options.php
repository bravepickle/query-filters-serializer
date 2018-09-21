<?php
/**
 * Date: 9/21/18
 * Time: 1:10 AM
 */

namespace QueryFilterSerializer\Config;

/**
 * Class Options contains options for QuerySerializer
 * @package QueryFilterSerializer\Filter
 */
class Options
{
    const DEFAULT_TABLE_NAME = 't';

    /**
     * @var array list of filter constraints
     */
    public $constraints = [];

    /**
     * @TODO use register method with DI instead of this implementation
     * @var string
     */
    public $constraintsNamespace;

    /**
     * @var string
     */
    public $constraintType = 'type';

    /**
     * @var string
     */
    public $constraintOptions = 'options';

    /**
     * @var bool
     */
    public $returnObject = false;

    /**
     * @var bool
     */
    public $buildSql = false;

    /**
     * @var string
     */
    public $tableName;

    /**
     * @var string
     */
    public $encoding = 'UTF-8';

    /**
     * List of specific encoders for filter types to inject dependencies to
     * Format: filter_type_name => encoder
     * Filter types must implement EncoderAwareInterface
     * @var array
     */
    public $filterTypeEncoders = [];

    /**
     * Options constructor.
     */
    public function __construct()
    {
        $this->tableName = self::DEFAULT_TABLE_NAME;
        $this->constraintsNamespace = 'QueryFilterSerializer\\Filter\\Type';
    }

}