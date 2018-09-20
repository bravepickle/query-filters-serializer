<?php
/**
 */

namespace QueryFilterSerializer\Filter\Serializer;


use QueryFilterSerializer\Filter\ParsingException;

class StringSerializer extends AbstractSerializer
{
    const NAME = 'string';

    const COND_DELIMITER = ';';
    const COND_NOT_STR = '!';
    const COND_EQUALS = 'eq';
    const COND_NOT_EQUALS = 'neq';

    protected $options = array(
        'allowed' => null,         // if has array with values, the allowed values should be checked.
        'delimiter' => self::COND_DELIMITER,
        'multiple' => false,
        'use_not' => false,
        'not_str' => self::COND_NOT_STR, // string
        'encoding' => 'UTF-8',
        'partial' => false,
    );



    public function serialize(array $data)
    {
        // TODO: Implement serialize() method.
    }

    public function unserialize($data)
    {
        if (!$data) {
            return array();
        }

        $values = $this->getOption('multiple') ? $this->explodeVals($data) : array($data);

        if ($this->getOption('allowed') !== null) {
            $unknown = array_diff($values, $this->getOption('allowed'));
            if ($unknown) {
                throw new ParsingException(sprintf('Values not allowed: %s. Expected: %s', implode(', ', $unknown),
                    implode(', ', $this->getOption('allowed'))));
            }
        }

        if ($this->getOption('use_not')) {
            return $this->parseIncludesExcludes($values);
        } else {
            return array(array('condition' => self::COND_EQUALS, 'value' => $values));
        }
    }

    protected function trimExcludeStr($value)
    {
        $strLen = mb_strlen($this->getOption('not_str'), $this->getOption('encoding'));

        return mb_substr($value, $strLen, null, $this->getOption('encoding'));
    }

    protected function isExclude($value)
    {
        return strpos($value, $this->getOption('not_str', self::COND_NOT_STR)) === 0;
    }

    /**
     * @param $data
     * @return array
     */
    protected function explodeVals($data)
    {
        return array_filter(array_unique(explode($this->getOption('delimiter', self::COND_DELIMITER), $data)));
    }

    /**
     * @param $values
     * @return array
     */
    protected function parseIncludesExcludes($values)
    {
        $includes = $excludes = array();
        foreach ($values as $val) {
            if ($this->isExclude($val)) {
                $excludes[] = $this->trimExcludeStr($val);
            } else {
                $includes[] = $val;
            }
        }

        $results = array();
        if ($includes) {
            $results[] = array('condition' => self::COND_EQUALS, 'value' => $includes);
        }
        if ($excludes) {
            $results[] = array('condition' => self::COND_NOT_EQUALS, 'value' => $excludes);
        }

        return $results;
    }

    /**
     * Create piece of SQL with placeholder and values
     * @param $filter
     * @param string $tableAlias
     * @return array
     * @throws ParsingException
     */
    public function buildSqlParts($filter, $tableAlias = 't')
    {
        $sql = [];
        $fieldPhBase = $tableAlias . '_' . $filter['field'];
        $num = 1;

        // TODO: add support of LIKE methods. Maybe add extra option to query string serializer?
        // TODO: add to QuerySerializer main class option - build SQL parts option?
        // TODO: return assoc arrays of strings with values instead of using builder ?
        foreach ($filter['constraints'] as $constraint) {
            $fieldPh = $num > 1 ? $fieldPhBase . $num : $fieldPhBase;
            $num++;
            switch ($constraint['condition']) {
                case StringSerializer::COND_NOT_EQUALS:
                    if (is_array($constraint['value']) && count($constraint['value']) > 1) {
                        $sql[] = [
                            'sql' => $tableAlias . '.' . $filter['field'] . ' NOT IN(:' . $fieldPh . ')',
                            'parameter' => [$fieldPh => $constraint['value']]
                        ];
                    } elseif ($this->getOption('partial', false)) {
                        $sql[] = [
                            'sql' => $tableAlias . '.' . $filter['field'] . ' NOT LIKE :' . $fieldPh,
                            'parameter' => [$fieldPh => '%' . reset($constraint['value']) . '%']
                        ];
                    } else {
                        $sql[] = [
                            'sql' => $tableAlias . '.' . $filter['field'] . ' != :' . $fieldPh,
                            'parameter' => [$fieldPh => reset($constraint['value'])]
                        ];
                    }
                    break;
                case StringSerializer::COND_EQUALS:
                    if (is_array($constraint['value']) && count($constraint['value']) > 1) {
                        $sql[] = [
                            'sql' => $tableAlias . '.' . $filter['field'] . ' IN(:' . $fieldPh . ')',
                            'parameter' => [$fieldPh => $constraint['value']]
                        ];
                    } elseif ($this->getOption('partial', false)) {
                        $sql[] = [
                            'sql' => $tableAlias . '.' . $filter['field'] . ' LIKE :' . $fieldPh,
                            'parameter' => [$fieldPh => '%' . reset($constraint['value']) . '%']
                        ];
                    } else {
                        $sql[] = [
                            'sql' => $tableAlias . '.' . $filter['field'] . ' = :' . $fieldPh,
                            'parameter' => [$fieldPh => reset($constraint['value'])]
                        ];
                    }
                    break;
                default:
                    throw new ParsingException('Undefined behavior for condition: ' . $constraint['condition']);
            }
        }

        return $sql;
    }
}
