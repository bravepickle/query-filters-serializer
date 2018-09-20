<?php
/**
 */

namespace QueryFilterSerializer\Filter\Type;


use QueryFilterSerializer\Filter\FieldFilter;
use QueryFilterSerializer\Filter\ParsingException;
use QueryFilterSerializer\Filter\QuerySerializer;
use QueryFilterSerializer\Filter\QuerySerializerAwareInterface;

class EmbeddedType extends AbstractType implements QuerySerializerAwareInterface
{
    const NAME = 'embedded';

    protected $options = array(
        self::OPT_CONSTRAINTS => [],
        self::OPT_TABLE_NAME => self::DEFAULT_TABLE_NAME,
    );

    const OPT_CONSTRAINTS = 'constraints'; // contains options for embedded object fields, e.g. for embedded user will be username, email etc.
    const WRAP_RIGHT = ')';
    const WRAP_LEFT = '(';
    const OPT_TABLE_NAME = 'table_name';
    const DEFAULT_TABLE_NAME = 't2';

    /**
     * @var QuerySerializer
     */
    protected $serializer;

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

    /**
     * @param $data
     * @return array
     * @throws ParsingException
     */
    public function unserialize($data)
    {
        $data = trim($data, self::WRAP_RIGHT . self::WRAP_LEFT); // remove wrapper
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
