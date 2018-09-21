<?php

namespace QueryFilterSerializer\Encoder;


use QueryFilterSerializer\Config\UrlQueryEncoderOptions;

/**
 * Class UrlQueryEncoder
 * Works with queries of format: "_filter[name]=value1&_filter[foo]=>bar;baz_filter[arr][]=v1"
 * @package QueryFilterSerializer\Encoder
 */
class UrlQueryEncoder implements EncoderInterface
{
    /**
     * @var UrlQueryEncoderOptions
     */
    protected $options;

    public function __construct(UrlQueryEncoderOptions $options = null)
    {
        $this->options = $options ?: new UrlQueryEncoderOptions();
    }

    /**
     * @param mixed $data
     * @param array $context
     * @return array
     */
    public function decode($data, $context = [])
    {
        $this->prepareContext($context);

        if (is_array($data)) {
            return $this->buildDecodeResult($data); // for now no changes. Later add filters checks based on allowed constraints
        }

        $decoded = null;
        parse_str($data, $decoded);

        // keep only our filters from parsed url query
        $decoded = isset($decoded[$this->options->filterName]) ? $decoded[$this->options->filterName] : [];

        return $this->buildDecodeResult($decoded);
    }

    public function encode($data, $context = [])
    {
        // TODO: Implement encode() method.
    }

    protected function buildDecodeResult(array $filters)
    {
        $data = [];
        foreach ($filters as $name => $values) {
            $data[] = array($name, $values);
        }

        return $data;
    }

    /**
     * @param $context
     */
    protected function prepareContext(&$context)
    {
        $context[EncoderInterface::CONTEXT_CONSTRAINTS] = empty($context[EncoderInterface::CONTEXT_CONSTRAINTS]) ? [] :
            $context[EncoderInterface::CONTEXT_CONSTRAINTS];
        $context[EncoderInterface::CONTEXT_ENCODING] = empty($context[EncoderInterface::CONTEXT_ENCODING]) ?
            null :
            $context[EncoderInterface::CONTEXT_ENCODING];
    }
}