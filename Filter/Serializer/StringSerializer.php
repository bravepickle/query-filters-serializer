<?php
/**
 */

namespace Filter\Serializer;


use Filter\ParsingException;

class StringSerializer extends AbstractSerializer
{
    const NAME = 'string';

    const COND_DELIMITER = ';';
    const COND_not_str = '!';

    protected $options = array(
        'allowed' => null,         // if has array with values, the allowed values should be checked.
        'delimiter' => self::COND_DELIMITER,
        'multiple' => false,
        'use_not' => false,
        'not_str' => self::COND_not_str, // string
        'encoding' => 'UTF-8',
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
            return array(array('condition' => 'eq', 'value' => $values));
        }
    }

    protected function trimExcludeStr($value)
    {
        $strLen = mb_strlen($this->getOption('not_str'), $this->getOption('encoding'));

        return mb_substr($value, $strLen, null, $this->getOption('encoding'));
    }

    protected function isExclude($value)
    {
        return strpos($value, $this->getOption('not_str', self::COND_not_str)) === 0;
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
            $results[] = array('condition' => 'eq', 'value' => $includes);
        }
        if ($excludes) {
            $results[] = array('condition' => 'neq', 'value' => $excludes);
        }

        return $results;
    }


}
