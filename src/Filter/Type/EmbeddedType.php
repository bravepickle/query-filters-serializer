<?php
/**
 */

namespace QueryFilterSerializer\Filter\Type;


use QueryFilterSerializer\Encoder\EncoderAwareInterface;
use QueryFilterSerializer\Encoder\EncoderInterface;
use QueryFilterSerializer\Encoder\Filter\StringEmbeddedTypeEncoder;
use QueryFilterSerializer\Exception\ParsingException;
use QueryFilterSerializer\Filter\FieldFilter;
use QueryFilterSerializer\Serializer\QuerySerializerAwareInterface;
use QueryFilterSerializer\Serializer\QuerySerializer;

class EmbeddedType extends AbstractType implements QuerySerializerAwareInterface, EncoderAwareInterface
{
    const NAME = 'embedded';
    const MAX_DEPTH = 1;
    const DEFAULT_TABLE_NAME = 't2';

    const OPT_ENCODER = 'encoder';
    const OPT_TABLE_NAME = 'table_name';
    const OPT_CONSTRAINTS = 'constraints';

    public $tableName = self::DEFAULT_TABLE_NAME;
    public $constraints = [];

    protected $options = array(
        // can be either string with encoder class name or EncoderInterface class instance
        self::OPT_ENCODER => StringEmbeddedTypeEncoder::class,
        // table name for embedded types
        self::OPT_TABLE_NAME => self::DEFAULT_TABLE_NAME,
        // embedded constraints
        self::OPT_CONSTRAINTS => [],
    );

    /**
     * @var QuerySerializer
     */
    protected $serializer;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * EmbeddedType constructor.
     * @param EncoderInterface $encoder
     */
    public function __construct(EncoderInterface $encoder = null)
    {
        $this->encoder = $encoder;
    }

    /**
     * @return EncoderInterface
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @param EncoderInterface|null $encoder
     */
    public function setEncoder(EncoderInterface $encoder = null)
    {
        $this->encoder = $encoder;
    }

    protected function initEncoder()
    {
        if (!$this->encoder) {
            $optEncoder = $this->getOption(self::OPT_ENCODER);
            $this->encoder = $optEncoder instanceof EncoderInterface ?
                $optEncoder : new $optEncoder();
        }
    }

    /**
     * @param QuerySerializer $serializer
     */
    public function setSerializer(QuerySerializer $serializer = null)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $data
     * @throws ParsingException
     */
    public function serialize(array $data)
    {
        throw new ParsingException('TODO: Implement ' . __METHOD__);
    }

//    protected function checkArrayDepth($data)
//    {
//        // TODO: calculate based on configs embedding level
//        $this->assertArrayMaxDepth($data, static::MAX_DEPTH);
//    }

    /**
     * @param $data
     * @return array
     * @throws ParsingException
     * @throws \QueryFilterSerializer\Exception\FilterException
     * @throws \QueryFilterSerializer\Exception\ArrayMaxDepthException
     */
    public function unserialize($data)
    {
//        $this->checkArrayDepth($data);
        $this->initEncoder();

        $data = $this->encoder->decode($data);
        if (!$data) {
            return array();
        }

        // backup
        $serializerConstraints = $this->serializer->getOptions()->constraints;
        $tableName = $this->serializer->getOptions()->tableName;

        // unserialize embedded
        $this->serializer->getOptions()->tableName = $this->getOption(self::OPT_TABLE_NAME, self::DEFAULT_TABLE_NAME);
        $this->serializer->getOptions()->constraints = $this->getOption(self::OPT_CONSTRAINTS, []);

        $unserialized = $this->serializer->unserialize($data); // pass embedded constraints

        // revert
        $this->serializer->getOptions()->constraints = $serializerConstraints;
        $this->serializer->getOptions()->tableName = $tableName;

        return $unserialized;
    }

    /**
     * Create piece of SQL with placeholder and values
     * @param $data
     * @param $tableAlias
     * @return array first element is string, the second - list of values
     */
    public function buildSqlParts($data, $tableAlias = null)
    {
        $output = [];

        if (isset($data['constraints'])) {
            foreach ($data['constraints'] as $fieldOpts) {
                if (isset($fieldOpts[FieldFilter::KEY_SQL_PARTS])) {
                    foreach ($fieldOpts[FieldFilter::KEY_SQL_PARTS] as $part) {
                       $output[] = $part;
                    }
                }
            }

        }

        return $output;
    }


}
