<?php
/**
 * Date: 9/21/18
 * Time: 10:06 PM
 */

namespace QueryFilterSerializer\Encoder\Filter;


use QueryFilterSerializer\Config\Filter\StringEmbeddedTypeOptions;
use QueryFilterSerializer\Encoder\EncoderInterface;
use QueryFilterSerializer\Exception\FilterException;
use QueryFilterSerializer\Exception\ParsingException;
use QueryFilterSerializer\Filter\Type\EmbeddedType;
use QueryFilterSerializer\Serializer\QuerySerializer;

/**
 * Class EmbeddedTypeEncoder encoder for parsing strings
 * @package QueryFilterSerializer\Encoder\Filter
 */
class StringEmbeddedTypeEncoder implements EncoderInterface
{
    /**
     * @var StringEmbeddedTypeOptions
     */
    protected $options;

    /**
     * StringEmbeddedTypeEncoder constructor.
     * @param StringEmbeddedTypeOptions|null $options
     */
    public function __construct(StringEmbeddedTypeOptions $options = null)
    {
        $this->options = $options ?: new StringEmbeddedTypeOptions();
    }

    /**
     * @param mixed string $data
     * @param array $context
     * @return array|mixed|string
     * @throws ParsingException
     * @throws FilterException
     */
    public function decode($data, $context = [])
    {
        if (!$data || !is_string($data)) {
            return array();
        }

        if (!is_string($data)) {
            throw new ParsingException('Unexpected data type for filter.');
        }

        if (empty($context[EncoderInterface::CONTEXT_SERIALIZER])) {
            throw new FilterException('Undefined serializer in context for filter: ' . EmbeddedType::NAME);
        }

//        /** @var QuerySerializer $serializer */
//        $serializer = $context[EncoderInterface::CONTEXT_SERIALIZER];
        $encoding = $context[EncoderInterface::CONTEXT_ENCODING];

        $len = mb_strlen($data, $encoding);
        $lastIndex = $len - 1;
        if ($data{1} !== $this->options->wrapLeft || $data{$lastIndex} !== $this->options->wrapRight) {
            throw new ParsingException('Failed to parse filter value.');
        }

        $data = mb_substr($data, 1, $len - 1);

        var_export($data);
        die("\n" . __METHOD__ . ":" . __FILE__ . ":" . __LINE__ . "\n");

        if (!$data) {
            return array();
        }

        // backup
        $serializerConstraints = $this->serializer->getOptions()->constraints;
        $tableName = $this->serializer->getOptions()->tableName;

        // unserialize embedded
        $this->serializer->getOptions()->tableName =
            $this->getOption(self::OPT_TABLE_NAME, self::DEFAULT_TABLE_NAME);
        $this->serializer->getOptions()->constraints = $this->getOption(self::OPT_CONSTRAINTS, []);

        $unserialized = $this->serializer->unserialize($data); // pass embedded constraints

        // revert
        $this->serializer->getOptions()->constraints = $serializerConstraints;
        $this->serializer->getOptions()->tableName = $tableName;

        return $unserialized;

        return $data;
    }

    public function encode($data, $context = [])
    {
        // TODO: Implement encode() method.
    }

}