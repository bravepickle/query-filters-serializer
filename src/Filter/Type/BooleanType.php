<?php
/**
 */

namespace QueryFilterSerializer\Filter\Type;


use QueryFilterSerializer\Filter\ParsingException;

class BooleanType extends AbstractType
{
    const NAME = 'boolean';

    const OPT_TRUE = 'true';
    const OPT_FALSE = 'false';
    const OPT_DEFAULT = 'default';

    const DEFAULT_TRUE = '1';
    const DEFAULT_FALSE = '0';
    const DEFAULT_VALUE = null;

    protected $options = array(
        self::OPT_TRUE => self::DEFAULT_TRUE,
        self::OPT_FALSE => self::DEFAULT_FALSE,
        self::OPT_DEFAULT => self::DEFAULT_VALUE,
        // default value to set if none is defined or some other value is set, boolean value, not choice value. By default, is not defined
    );

    public function serialize(array $data)
    {
        foreach ($data as $value) {
            if ($value === true) {
                return $this->getOption(self::OPT_TRUE, self::DEFAULT_TRUE);
            } elseif ($value === false) {
                return $this->getOption(self::OPT_FALSE, self::DEFAULT_FALSE);
            } elseif ($this->getOption(self::OPT_DEFAULT, self::DEFAULT_VALUE) === null) {
                return !empty($value) ? $this->getOption(self::OPT_TRUE, self::DEFAULT_TRUE) :
                    $this->getOption(
                        self::OPT_FALSE,
                        self::DEFAULT_FALSE
                    ); // if default value is not defined then just standard check
            } else {
                return $this->getOption(self::OPT_DEFAULT, self::DEFAULT_VALUE) === $this->getOption(
                    self::OPT_TRUE,
                    self::DEFAULT_TRUE
                ) ?
                    $this->getOption(self::OPT_TRUE, self::DEFAULT_TRUE) :
                    $this->getOption(self::OPT_FALSE, self::DEFAULT_FALSE);
            }
        }

        return $this->getOption(self::OPT_DEFAULT, self::DEFAULT_VALUE);
    }

    /**
     * @param $data
     * @return array
     * @throws ParsingException
     */
    public function unserialize($data)
    {
        if ($this->getOption(self::OPT_TRUE, self::DEFAULT_TRUE) === $data) {
            return array(array('condition' => 'eq', 'value' => true));
        } elseif ($this->getOption(self::OPT_FALSE, self::DEFAULT_FALSE) === $data) {
            return array(array('condition' => 'eq', 'value' => false));
        } elseif ($this->getOption(self::OPT_DEFAULT, self::DEFAULT_VALUE) !== null) {
            return array(
                array(
                    'condition' => 'eq',
                    'value' => $this->getOption(self::OPT_DEFAULT, self::DEFAULT_VALUE)
                )
            );
        } else {
            throw new ParsingException('Not defined value: (' . gettype($data) . ') ' . $data);
        }
    }

    /**
     * Create piece of SQL with placeholder and values
     * @param $data
     * @param $tableAlias
     * @return array first element is string, the second - list of values
     */
    public function buildSqlParts($data, $tableAlias = 't')
    {
        $sql = [];

        if (!isset($data['constraints'][0])) {
            return $sql;
        }

        $fieldPh = $tableAlias . '_' . $data['field'];

        $sql[] = [
            'sql' => $tableAlias . '.' . $data['field'] . ' = :' . $fieldPh,
            'parameter' => [$fieldPh => (bool)$data['constraints'][0]['value']]
        ];

//        \Doctrine\Common\Util\Debug::dump($sql, 4);die("\n" . __METHOD__ . ':' . __LINE__ . "\n");

        return $sql;
    }


}
