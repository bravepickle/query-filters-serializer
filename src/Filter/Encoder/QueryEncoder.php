<?php

namespace QueryFilterSerializer\Filter\Encoder;


use QueryFilterSerializer\Filter\Config\QueryEncoderOptions;
use QueryFilterSerializer\Filter\EncoderInterface;
use QueryFilterSerializer\Filter\ParsingException;
use QueryFilterSerializer\Filter\Type\EmbeddedType;

class QueryEncoder implements EncoderInterface
{
    /**
     * @var QueryEncoderOptions
     */
    protected $options;

    public function __construct(QueryEncoderOptions $options = null)
    {
        $this->options = $options ?: new QueryEncoderOptions();
    }

    /**
     * @param mixed $data
     * @param array $context
     * @return array
     * @throws ParsingException
     */
    public function decode($data, $context = [])
    {
        $context[EncoderInterface::CONTEXT_CONSTRAINTS] = empty($context[EncoderInterface::CONTEXT_CONSTRAINTS]) ? [] :
            $context[EncoderInterface::CONTEXT_CONSTRAINTS];
        $context[EncoderInterface::CONTEXT_ENCODING] = empty($context[EncoderInterface::CONTEXT_ENCODING]) ?
            null :
            $context[EncoderInterface::CONTEXT_ENCODING];

        return $this->explodeConstraints($data, $context);
    }

    public function encode($data, $context = [])
    {
        // TODO: Implement encode() method.
    }

    /**
     * @param $query
     * @param array $context
     * @return array
     * @throws ParsingException
     */
    protected function explodeConstraints($query, array $context)
    {
        $placeholder = '%!$_TMP_$!%';
        $placeholderEsc = '%_$_TMP_$_%';
        $placeholderEmbed = '%_$_EMB_$_%';

        $delim = $this->options->constraintDelimiter;

        $esc = $this->options->escapeStr;
        $tpl = str_replace(array($esc . $esc, $esc . $delim), array($placeholderEsc, $placeholder), $query);

        $this->setPlaceholdersForEmbedded($delim, $tpl, $placeholderEmbed, $context[EncoderInterface::CONTEXT_CONSTRAINTS], $replaces);

        $values = explode($delim, $tpl);

        // revert placeholders to values
        foreach ($values as &$v) {
            if ($replaces) {
                foreach ($replaces as $search => &$replace) {
                    $v = str_replace($search, $replace, $v);
                }
            }

            $v = str_replace(array($placeholderEsc, $placeholder, $placeholderEmbed), array($esc . $esc, $delim, $delim), $v);

        }

        $values = array_unique(array_filter($values));
        foreach ($values as &$value) {
            $value = $this->splitKeyValue($value, $context);
        }


        return $values;
    }

    /**
     * @param $value
     * @param array $context
     * @return array
     * @throws ParsingException
     */
    protected function splitKeyValue($value, array $context)
    {
        if (!$this->canSplitKeyValue($value, $context)) {
            throw new ParsingException('Filter constraint is not defined correctly');
        }

        $strPos = mb_strpos(
            $value,
            $this->options->nameValueDelimiter,
            null,
            $context[EncoderInterface::CONTEXT_ENCODING]
        );
        $strLen = mb_strlen($this->options->nameValueDelimiter, $context[EncoderInterface::CONTEXT_ENCODING]);

        return array(
            mb_substr($value, 0, $strPos, $context[EncoderInterface::CONTEXT_ENCODING]),
            mb_substr($value, $strPos + $strLen, null, $context[EncoderInterface::CONTEXT_ENCODING]),
        );
    }

    protected function canSplitKeyValue($value, array $context)
    {
        return mb_strpos(
                $value,
                $this->options->nameValueDelimiter,
                null,
                $context[EncoderInterface::CONTEXT_ENCODING]
            ) !== false;
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

            // TODO: parse embedded and put them here before decoding...
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
                        foreach ($matches[2] as $key => &$subQuery) {
                            $templated = str_replace($delim, $placeholderEmbed, $subQuery);
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