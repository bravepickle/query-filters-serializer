<?php
/**
 * User: victor
 * Date: 25.05.14
 * Time: 19:46
 */

namespace QueryFilterSerializer\Filter;

use QueryFilterSerializer\Filter\Type\EmbeddedType;

class QuerySerializer implements SerializerInterface
{
    const OPT_NAME_VALUE_DELIMITER = 'field_value_delimiter';
    const OPT_CONSTRAINT_DELIMITER = 'constraint_delimiter';
    const OPT_CONSTRAINTS = 'constraints';
    const OPT_CONSTRAINTS_NAMESPACE = 'constraints_namespace';
    const OPT_CONSTRAINT_TYPE = 'constraint_type'; // type of constraint
    const OPT_CONSTRAINT_OPTIONS = 'constraint_options'; // options of constraint
    const OPT_RETURN_OBJECT = 'return_object'; // return objects instead of arrays
    const OPT_ESCAPE_STR = 'escape_str'; // escape string sequence
    const OPT_BUILD_SQL_PARTS = 'build_sql'; // should the serializer build additionally DQL parts with params?
    const OPT_TABLE_NAME = 'table_name';
    const OPT_ENCODING = 'encoding';
    const DEFAULT_TABLE_NAME = 't';

    protected $options = array(
        self::OPT_NAME_VALUE_DELIMITER => ':', // value-name delimiter
        self::OPT_CONSTRAINT_DELIMITER => '|', // constraint delimiter between each filter
        self::OPT_CONSTRAINTS => array(), // constraints block name
        self::OPT_CONSTRAINTS_NAMESPACE => '\QueryFilterSerializer\Filter\Type', // constraints namespace
        self::OPT_CONSTRAINT_TYPE => 'type',
        self::OPT_CONSTRAINT_OPTIONS => 'options',
        self::OPT_RETURN_OBJECT => false,
        self::OPT_BUILD_SQL_PARTS => false,
        self::OPT_ENCODING => 'UTF-8',
        self::OPT_ESCAPE_STR => '\\',
        self::OPT_TABLE_NAME => self::DEFAULT_TABLE_NAME,
    );

    /**
     * @var array
     */
    protected $filterTypes = array();

    public function __construct($options = array())
    {
        $this->options = array_merge($this->options, $options); // TODO: array merge
    }

    /**
     * @param $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
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
     * @param $name
     * @param $value
     * @return $this
     * @throws ParsingException
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new ParsingException('Unknown option given: ' . $name);
        }
        $this->options[$name] = $value;

        return $this;
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
                throw new ParsingException('Failed to read options on query filter field: ' . $name);
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
     * @return QueryFilterTypeInterface
     */
    public function getSerializerTypeByName($name)
    {
        $fullClassName = $this->options[self::OPT_CONSTRAINTS_NAMESPACE] . '\\' . ucwords($name) . 'Type';

        if (isset($this->filterTypes[$fullClassName])) {
            return $this->filterTypes[$fullClassName];
        }

        $this->filterTypes[$fullClassName] = new $fullClassName();

        if ($this->filterTypes[$fullClassName] instanceof QuerySerializerAwareInterface) {
            $this->filterTypes[$fullClassName]->setSerializer($this);
        }

        return clone $this->filterTypes[$fullClassName];
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
     * @param QueryFilterTypeInterface $typeSerializer
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

        // define field name by alias or leave as it is
        $pairs[$name]['field'] = isset($this->options[self::OPT_CONSTRAINTS][$name]['name']) ?
            $this->options[self::OPT_CONSTRAINTS][$name]['name'] : $name;

        if ($this->options[self::OPT_BUILD_SQL_PARTS]) { // build SQL parts?
            $pairs[$name][FieldFilter::KEY_SQL_PARTS] = $typeSerializer->buildSqlParts($pairs[$name], $this->getOption(self::OPT_TABLE_NAME, self::DEFAULT_TABLE_NAME));

            // TODO: update fieldfilter objects to support DQLs!
        }

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
        $placeholderEmbed = '%_$_EMB_$_%';
        $delim = $this->options[self::OPT_CONSTRAINT_DELIMITER];

        $esc = $this->getOption(self::OPT_ESCAPE_STR);
        $tpl = str_replace(array($esc . $esc, $esc . $delim), array($placeholderEsc, $placeholder), $query);

        $this->setPlaceholdersForEmbedded($delim, $tpl, $placeholderEmbed, $this->getOption(self::OPT_CONSTRAINTS), $replaces);

        $vals = explode($delim, $tpl);

        // revert placeholders to values
        foreach ($vals as &$v) {
            if ($replaces) {
                foreach ($replaces as $search => &$replace) {
                    $v = str_replace($search, $replace, $v);
                }
            }

            $v = str_replace(array($placeholderEsc, $placeholder, $placeholderEmbed), array($esc . $esc, $delim, $delim), $v);
        }

        return $vals;
    }

    /**
     * @param $delim
     * @param $tpl
     * @param $placeholderEmbed
     * @param $constraints
     * @param $replaces
     * @return null|array
     */
    protected function setPlaceholdersForEmbedded($delim, &$tpl, $placeholderEmbed, $constraints, &$replaces = null)
    {
        $fieldTypes = array_column($constraints, 'type');
        if (in_array(EmbeddedType::NAME, $fieldTypes)) { // do we have embedded queries?
            // TODO: dive in deeper... recursion

            // after recursion...

            $quotedDelim = preg_quote($delim, '~');
            foreach ($constraints as $field => &$options) {
                // is embedded
                if (isset($options['type']) && $options['type'] == EmbeddedType::NAME) {
                    // TODO: embedded recursion...
                    // children have also embedded fields
//                    if (isset($options['options'][EmbeddedSerializer::OPT_CONSTRAINTS])) {
//                        $subMatches = $this->setPlaceholdersForEmbedded($delim, $tpl, $placeholderEmbed, $options['options'][EmbeddedSerializer::OPT_CONSTRAINTS], $replaces);
//                        $this->addReplaces($replaces, $subMatches);
//                    }

                    $quotedName = preg_quote($field, '~');
                    $wrapLeft = preg_quote(EmbeddedType::WRAP_LEFT, '~');
                    $wrapRight = preg_quote(EmbeddedType::WRAP_RIGHT, '~');
                    $pattern = "~($quotedName:$wrapLeft)" .
                        "([^$wrapRight]*{$quotedDelim}[^$wrapRight]*)" .
                        "($wrapRight(?:$quotedDelim|$))~iU";
                    if (preg_match_all($pattern, $tpl, $matches)) {
                        foreach ($matches[2] as $key => &$subquery) {
                            $templated = str_replace($delim, $placeholderEmbed, $subquery);
                            $tpl = str_replace($matches[0][$key], $matches[1][$key] . $templated . $matches[3][$key], $tpl);
                        }

                        return $matches;
                    }
                }
            }
        }

        return null;
    }

//    protected function addReplaces(&$replaces, $matches)
//    {
//      TODO: add replaces of query name:(embedded_params) to name:(_TMP_1_)
//        print_r($matches);
//        print_r($replaces);
//        die("\n" . __METHOD__ . ':' . __LINE__ . "\n");
//    }
}