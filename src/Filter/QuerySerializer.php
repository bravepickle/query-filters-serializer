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
    /**
     * @var Options
     */
    protected $options;

    /**
     * @var array
     */
    protected $filterTypes = array();

    /**
     * QuerySerializer constructor.
     * @param Options|null $options
     */
    public function __construct(Options $options = null)
    {
        $this->options = $options !== null ? $options : new Options();
    }

    /**
     * @param Options|null $options
     * @return $this
     */
    public function setOptions(Options $options = null)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
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

            $modType = $this->options->constraintType;

            if (!isset($this->options->constraints[$name][$modType])) {
                throw new ParsingException('Failed to read options on query filter field: ' . $name);
            }

            list($typeSerializer, $parsed) = $this->parseFilterData($name, $modType, $value);

            if ($parsed) {
                $pairs = $this->initFieldFilterArr($pairs, $name, $parsed, $typeSerializer);
            }
        }

        if ($this->options->returnObject) {
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
        $fullClassName = $this->options->constraintsNamespace . '\\' . ucwords($name) . 'Type';

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
        $pairs[$name]['field'] = isset($this->options->constraints[$name]['name']) ?
            $this->options->constraints[$name]['name'] : $name;

        if ($this->options->buildSql) { // build SQL parts?
            $pairs[$name][FieldFilter::KEY_SQL_PARTS] = $typeSerializer->buildSqlParts($pairs[$name], $this->options->tableName);

            // TODO: update FieldFilter objects to support DQLs!
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
        $typeSerializer = $this->getSerializerTypeByName($this->options->constraints[$name][$modType]);

        $modOpt = $this->options->constraintOptions;

        if (isset($this->options->constraints[$name][$modOpt])) {
            $typeSerializer->setOptions($this->options->constraints[$name][$modOpt]);
        }

        $parsed = $typeSerializer->unserialize($value);

        return array($typeSerializer, $parsed);
    }

    /**
     * @param $value
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
            $this->options->nameValueDelimiter,
            null,
            $this->options->encoding
        );
        $strLen = mb_strlen($this->options->nameValueDelimiter, $this->options->encoding);

        return array(
            mb_substr($value, 0, $strPos, $this->options->encoding),
            mb_substr($value, $strPos + $strLen, null, $this->options->encoding),
        );
    }

    protected function canSplitKeyValue($value)
    {
        return mb_strpos(
            $value,
            $this->options->nameValueDelimiter,
            null,
            $this->options->encoding
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
        $delim = $this->options->constraintDelimiter;

        $esc = $this->options->escapeStr;
        $tpl = str_replace(array($esc . $esc, $esc . $delim), array($placeholderEsc, $placeholder), $query);

        $this->setPlaceholdersForEmbedded($delim, $tpl, $placeholderEmbed, $this->options->constraints, $replaces);

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