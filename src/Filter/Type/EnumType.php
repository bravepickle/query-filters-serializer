<?php
/**
 */

namespace QueryFilterSerializer\Filter\Type;


use QueryFilterSerializer\Exception\ParsingException;

class EnumType extends StringType
{
    const NAME = 'enum';

    const COND_DELIMITER = ';';
    const COND_NOT_STR = '!';

    protected $options = array(
        'allowed' => [],         // if has array with values, the allowed values should be checked.
        'delimiter' => self::COND_DELIMITER,
        'multiple' => true,
        'use_not' => false,
        'not_str' => self::COND_NOT_STR, // string
        'encoding' => 'UTF-8',
    );


    /**
     * Create piece of SQL with placeholder and values
     * @param $data
     * @param string $tableAlias
     * @return void first element is string, the second - list of values
     * @throws ParsingException
     */
    public function buildSqlParts($data, $tableAlias = 't')
    {
        throw new ParsingException('TODO: Implement buildSqlParts() method: ' . __CLASS__);
    }
}
