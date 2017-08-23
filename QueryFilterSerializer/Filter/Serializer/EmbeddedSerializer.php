<?php
/**
 */

namespace QueryFilterSerializer\Filter\Serializer;


use QueryFilterSerializer\Filter\ParsingException;
use QueryFilterSerializer\Filter\QuerySerializer;
use QueryFilterSerializer\Filter\QuerySerializerAwareInterface;

class EmbeddedSerializer extends AbstractSerializer implements QuerySerializerAwareInterface
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


    public function serialize(array $data)
    {
        throw new ParsingException('TODO: Implement ' . __METHOD__);
    }

    public function unserialize($data)
    {
        $data = trim($data, self::WRAP_RIGHT . self::WRAP_LEFT); // remove wrapper
        if (!$data) {
            return array();
        }

        // backup
        $serializerConstraints = $this->serializer->getOption(QuerySerializer::OPT_CONSTRAINTS, []);
        $tableName = $this->serializer->getOption(QuerySerializer::OPT_TABLE_NAME);

        // unserialize embedded
        $this->serializer->setOption(QuerySerializer::OPT_TABLE_NAME, $this->getOption(self::OPT_TABLE_NAME, self::DEFAULT_TABLE_NAME));
        $unserialized = $this->serializer->unserialize($data, $this->getOption(self::OPT_CONSTRAINTS, [])); // pass embedded constraints

        // revert
        $this->serializer->setOption(QuerySerializer::OPT_CONSTRAINTS, $serializerConstraints);
        $this->serializer->setOption(QuerySerializer::OPT_TABLE_NAME, $tableName);

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
                if (isset($fieldOpts['sql_parts'])) {
                    foreach ($fieldOpts['sql_parts'] as $part) {
                       $output[] = $part;
                    }
                }
            }

        }

        return $output;
    }


}
