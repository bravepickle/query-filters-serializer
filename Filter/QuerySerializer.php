<?php
/**
 * User: victor
 * Date: 25.05.14
 * Time: 19:46
 */

namespace Filter;

class QuerySerializer
{
    const OPT_NAME_VALUE_DELIMITER = 'field_value_delimiter';
    const OPT_CONSTRAINT_DELIMITER = 'constraint_delimiter';
    const OPT_CONSTRAINTS = 'constraints';
    const OPT_CONSTRAINTS_NAMESPACE = 'constraints_namespace';
    const OPT_CONSTRAINT_TYPE = 'constraint_type'; // type of constraint
    const OPT_CONSTRAINT_OPTIONS = 'constraint_options'; // options of constraint
    const OPT_RETURN_OBJECT = 'return_object'; // return objects instead of arrays
    const OPT_ESCAPE_STR = 'escape_str'; // escape string sequence

    protected $options = array(
        self::OPT_NAME_VALUE_DELIMITER => ':', // value-name delimiter
        self::OPT_CONSTRAINT_DELIMITER => '|', // constraint delimiter between each filter
        self::OPT_CONSTRAINTS => array(), // constraints block name
        self::OPT_CONSTRAINTS_NAMESPACE => '\Filter\Serializer', // constraints namespace
        self::OPT_CONSTRAINT_TYPE => 'type',
        self::OPT_CONSTRAINT_OPTIONS => 'options',
        self::OPT_RETURN_OBJECT => false,
        'encoding' => 'UTF-8',
        self::OPT_ESCAPE_STR => '\\',
    );

    protected $serializers = array();

    public function __construct($options = array())
    {
        $this->options = array_merge($this->options, $options); // TODO: array merge
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * Parse query into options list
     * @param $query
     * @return array
     * @throws ParsingException
     */
    public function unserialize($query)
    {
        if (!$query) {
            return array(); // no query parameters
        }

        $constraints = array_unique(array_filter($this->explodeConstraints($query)));

        $pairs = array();

        foreach ($constraints as $constraint) {
            $sub = $this->splitKeyValue($constraint);
            list($name, $value) = $sub;

            $modType = $this->options[self::OPT_CONSTRAINT_TYPE];

            if (!isset($this->options[self::OPT_CONSTRAINTS][$name][$modType])) {
                throw new ParsingException('Constraint options are not defined correctly: ' . $name);
            }

            list($typeSerializer, $parsed) = $this->parseFilterData($name, $modType, $value);

            if ($parsed) {
                $pairs = $this->initFieldFilterArr($pairs, $name, $parsed, $typeSerializer);
            }
        }

        if ($this->options[self::OPT_RETURN_OBJECT]) {
            return $this->wrapArrayToConstraints($pairs);
        }

        return $pairs;
    }

    public function serialize(array $filters)
    {
        // TODO:
    }

    /**
     * @param $name
     * @return \Filter\Serializer\AbstractSerializer
     */
    public function getSerializerTypeByName($name)
    {
        $fullClassName = $this->options[self::OPT_CONSTRAINTS_NAMESPACE] . '\\' . ucwords($name) . 'Serializer';

        if (isset($this->serializers[$fullClassName])) {
            return $this->serializers[$fullClassName];
        }

        $this->serializers[$fullClassName] = new $fullClassName();

        return $this->serializers[$fullClassName];
    }

    /**
     * @param $pairs
     * @return array
     */
    protected function wrapArrayToConstraints($pairs)
    {
        $objects = array();
        foreach ($pairs as $name => $arr) {
            $objects[$name] = new FieldFilter($arr);
        }

        return $objects;
    }

    /**
     * @param $pairs
     * @param $name
     * @param $parsed
     * @param $typeSerializer
     * @return mixed
     */
    protected function initFieldFilterArr($pairs, $name, $parsed, $typeSerializer)
    {
        $pairs[$name] = array();
        if (!isset($pairs[$name])) {
            $pairs[$name]['constraints'] = $parsed;
        } else {
            $pairs[$name]['constraints'] = array_merge($pairs[$name], $parsed);
        }
        $pairs[$name]['type'] = $typeSerializer->getName();
        $pairs[$name]['field'] = $name;

        return $pairs;
    }

    /**
     * @param $name
     * @param $modType
     * @param $value
     * @return array
     */
    protected function parseFilterData($name, $modType, $value)
    {
        $typeSerializer = $this->getSerializerTypeByName($this->options[self::OPT_CONSTRAINTS][$name][$modType]);

        $modOpt = $this->options[self::OPT_CONSTRAINT_OPTIONS];

        if (isset($this->options[self::OPT_CONSTRAINTS][$name][$modOpt])) {
            $typeSerializer->setOptions($this->options[self::OPT_CONSTRAINTS][$name][$modOpt]);
        }

        $parsed = $typeSerializer->unserialize($value);

        return array($typeSerializer, $parsed);
    }

    /**
     * @param $constraint
     * @return array
     * @throws ParsingException
     */
    protected function splitKeyValue($value)
    {
        if (!$this->canSplitKeyValue($value)) {
            throw new ParsingException('Filter constraint is not defined correctly');
        }

        $strPos = mb_strpos(
            $value,
            $this->getOption(self::OPT_NAME_VALUE_DELIMITER),
            null,
            $this->getOption('encoding')
        );
        $strLen = mb_strlen($this->options[self::OPT_NAME_VALUE_DELIMITER], $this->getOption('encoding'));

        return array(
            mb_substr($value, 0, $strPos, $this->getOption('encoding')),
            mb_substr($value, $strPos + $strLen, null, $this->getOption('encoding')),
        );
    }

    protected function canSplitKeyValue($value)
    {
        return mb_strpos(
            $value,
            $this->getOption(self::OPT_NAME_VALUE_DELIMITER),
            null,
            $this->getOption('encoding')
        ) !== false;
    }

    /**
     * @param $query
     * @return array
     */
    protected function explodeConstraints($query)
    {
        $placeholder = '%!$_TMP_$!%';
        $placeholderEsc = '%_$_TMP_$_%';
        $delim = $this->options[self::OPT_CONSTRAINT_DELIMITER];

        $esc = $this->getOption(self::OPT_ESCAPE_STR);
        $tpl = str_replace(array($esc . $esc, $esc . $delim), array($placeholderEsc, $placeholder), $query);

        // ensure escaping support of symbols
        $vals = explode($this->options[self::OPT_CONSTRAINT_DELIMITER], $tpl);
        foreach ($vals as &$v) {
            $v = str_replace(array($placeholderEsc, $placeholder), array($esc . $esc, $delim), $v);
        }

        return $vals;
    }
}