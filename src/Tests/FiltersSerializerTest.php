<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use QueryFilterSerializer\Filter\QuerySerializer;

class FiltersSerializerTest extends \PHPUnit\Framework\TestCase
{
    public function providerAge()
    {
        return array(
            array(
                'age:>0|count:0;1;2|sold:0',
                array(
                    'constraints' => array(
                        'age' => array('type' => 'integer'),
                        'count' => array('type' => 'integer'),
                        'sold' => array('type' => 'integer'),
                    )
                ),
                array(
                    'age' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'gt',
                                'value' => 0,
                            )
                        ),
                        'field' => 'age',
                    ),
                    'count' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array(0, 1, 2),
                            )
                        ),
                        'field' => 'count',
                    ),
                    'sold' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array(0),
                            )
                        ),
                        'field' => 'sold',
                    ),
                )
            ),
            array(
                'age:18',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('18'),
                            )
                        ),
                        'field' => 'age',
                    )
                )
            ),
            array(
                'age:!18',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'neq',
                                'value' => array('18'),
                            )
                        ),
                        'field' => 'age',
                    )
                )
            ),
            array(
                'age:18;20;21;20;;0',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'field' => 'age',
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('18', '20', '21', '0'),
                            )
                        ),
                    )
                )
            ),
            array(
                'age:>16',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'field' => 'age',
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'gt',
                                'value' => 16,
                            )
                        ),
                    )
                )
            ),
            array(
                'age:<=23',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'field' => 'age',
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'lte',
                                'value' => 23,
                            )
                        ),
                    )
                )
            ),
            array(
                'age:<23;<2;<10',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'field' => 'age',
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'lt',
                                'value' => 2,
                            )
                        ),
                    )
                )
            ),
            array(
                'age:>4;>20;>7',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'field' => 'age',
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'gt',
                                'value' => 20,
                            )
                        ),
                    )
                )
            ),
            array(
                'age:>=14;<18;16;=17',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'field' => 'age',
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'gte',
                                'value' => 14,
                            ),
                            array(
                                'condition' => 'lt',
                                'value' => 18,
                            ),
                            array(
                                'condition' => 'eq',
                                'value' => array(16, 17),
                            ),
                        ),
                    )
                )
            ),
            array(
                'age:18',
                array('return_object' => true, 'constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => new QueryFilterSerializer\Filter\FieldFilter(
                        array(
                            'type' => 'integer',
                            'constraints' => array(
                                new QueryFilterSerializer\Filter\FieldConstraint(
                                    array(
                                        'condition' => 'eq',
                                        'value' => array('18'),
                                    )
                                )
                            ),
                            'field' => 'age',
                        )
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider providerAge
     * @param $filter
     * @param $options
     * @param $expected
     * @throws \QueryFilterSerializer\Filter\ParsingException
     */
    public function testSerializeAge($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($this->genOptions($options));
        $serialization = $serializer->unserialize($filter);

        $this->assertEquals($expected, $serialization);
    }

    public function providerName()
    {
        return array(
            array(
                'name:John, hello',
                array('constraints' => array('name' => array('type' => 'string',))),
                array(
                    'name' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('John, hello'),
                            )
                        ),
                        'field' => 'name',
                    )
                )
            ),
            array(
                'name:John;hello;!Jake;Jim!',
                array(
                    'constraints' => array(
                        'name' => array(
                            'type' => 'string',
                            'options' => array('use_not' => true, 'multiple' => true)
                        )
                    )
                ),
                array(
                    'name' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('John', 'hello', 'Jim!'),
                            ),
                            array(
                                'condition' => 'neq',
                                'value' => array('Jake'),
                            ),
                        ),
                        'field' => 'name',
                    )
                )
            ),
            array(
                'name:"I hope; this: will, wo\|rk',
                array('constraints' => array('name' => array('type' => 'string',))),
                array(
                    'name' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('"I hope; this: will, wo|rk'),
                            )
                        ),
                        'field' => 'name',
                    )
                )
            ),
            array(
                'fist_name:!not',
                array(
                    'constraints' => array(
                        'fist_name' => array(
                            'type' => 'string',
                            'options' => array('use_not' => true)
                        )
                    )
                ),
                array(
                    'fist_name' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'neq',
                                'value' => array('not'),
                            )
                        ),
                        'field' => 'fist_name',
                    )
                ),
            ),
            array(
                'status:active',
                array(
                    'constraints' => array(
                        'status' => array(
                            'type' => 'string',
                            'options' => array('allowed' => array('active', 'passive'))
                        )
                    )
                ),
                array(
                    'status' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('active'),
                            )
                        ),
                        'field' => 'status',
                    )
                ),
            ),
        );
    }

    /**
     * @dataProvider providerName
     * @param $filter
     * @param $options
     * @param $expected
     * @throws \QueryFilterSerializer\Filter\ParsingException
     */
    public function testSerializeName($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($this->genOptions($options));
        $serialization = $serializer->unserialize($filter);

        $this->assertEquals($expected, $serialization);
    }


    public function providerComposite()
    {
        return array(
            array(
                'email:user@example.com|id:<10;>=2',
                array(
                    'constraints' => array(
                        'email' => array('type' => 'string',),
                        'id' => array('type' => 'integer')
                    )
                ),
                array(
                    'email' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('user@example.com'),
                            )
                        ),
                        'field' => 'email',
                    ),
                    'id' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'lt',
                                'value' => 10,
                            ),
                            array(
                                'condition' => 'gte',
                                'value' => 2,
                            )
                        ),
                        'field' => 'id',
                    ),

                )
            ),
        );
    }

    /**
     * @dataProvider providerComposite
     * @param $filter
     * @param $options
     * @param $expected
     * @throws \QueryFilterSerializer\Filter\ParsingException
     */
    public function testSerializeComposite($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($this->genOptions($options));
        $serialization = $serializer->unserialize($filter);

        $this->assertEquals($expected, $serialization);
    }

    public function providerBool()
    {
        return array(
            array(
                'is_new:1',
                array('constraints' => array('is_new' => array('type' => 'boolean',))),
                array(
                    'is_new' => array(
                        'type' => 'boolean',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => true,
                            )
                        ),
                        'field' => 'is_new',
                    ),
                )
            ),
            array(
                'good:0',
                array('constraints' => array('good' => array('type' => 'boolean',))),
                array(
                    'good' => array(
                        'type' => 'boolean',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => false,
                            )
                        ),
                        'field' => 'good',
                    ),
                )
            ),
        );
    }

    /**
     * @dataProvider providerBool
     * @param $filter
     * @param $options
     * @param $expected
     * @throws \QueryFilterSerializer\Filter\ParsingException
     */
    public function testSerializeBool($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($this->genOptions($options));
        $serialization = $serializer->unserialize($filter);

        $this->assertEquals($expected, $serialization);
    }

    public function providerEnum()
    {
        return array(
            array(
                'status:active;pending',
                array(
                    'constraints' => array(
                        'status' => array(
                            'type' => 'enum',
                            'options' => array('allowed' => array('active', 'pending', 'disabled'))
                        )
                    )
                ),
                array(
                    'status' => array(
                        'type' => 'enum',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('active', 'pending'),
                            )
                        ),
                        'field' => 'status',
                    ),
                )
            ),
        );
    }

    /**
     * @dataProvider providerEnum
     * @param $filter
     * @param $options
     * @param $expected
     * @throws \QueryFilterSerializer\Filter\ParsingException
     */
    public function testSerializeEnum($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($this->genOptions($options));
        $serialization = $serializer->unserialize($filter);

        $this->assertEquals($expected, $serialization);
    }

    public function providerSql()
    {
        return array(
            array(
                'text:active;pending|name:first;second;!third',
                array(
                    'constraints' => array(
                        'text' => array(
                            'type' => 'string',
                            'options' => array('partial' => true,),
                        ),
                        'name' => array(
                            'type' => 'string',
                            'options' => array('multiple' => true, 'use_not' => true),
                        ),
                    ),
                    'build_sql' => true,
                ),
                array(
                    'text' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('active;pending'),
                            )
                        ),
                        'field' => 'text',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.text LIKE :t_text',
                                'parameter' => array('t_text' => '%active;pending%'),
                            )
                        ),
                    ),
                    'name' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('first', 'second'),
                            ),
                            array(
                                'condition' => 'neq',
                                'value' => array('third'),
                            ),
                        ),
                        'field' => 'name',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.name IN(:t_name)',
                                'parameter' => array('t_name' => array('first', 'second')),
                            ),
                            array(
                                'sql' => 't.name != :t_name2',
                                'parameter' => array('t_name2' => 'third'),
                            ),
                        ),
                    ),
                )
            ),
            array(
                'title:!Lorem',
                array(
                    'constraints' => array(
                        'title' => array(
                            'type' => 'string',
                            'options' => array('partial' => true, 'use_not' => true,)
                        )
                    ),
                    'build_sql' => true,
                ),
                array(
                    'title' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'neq',
                                'value' => array('Lorem'),
                            )
                        ),
                        'field' => 'title',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.title NOT LIKE :t_title',
                                'parameter' => array('t_title' => '%Lorem%'),
                            )
                        ),
                    ),
                )
            ),
            array(
                'title:!Lorem',
                array(
                    'constraints' => array(
                        'title' => array(
                            'type' => 'string',
                            'options' => array('partial' => true,)
                        )
                    ),
                    'build_sql' => true,
                ),
                array(
                    'title' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('!Lorem'),
                            )
                        ),
                        'field' => 'title',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.title LIKE :t_title',
                                'parameter' => array('t_title' => '%!Lorem%'),
                            )
                        ),
                    ),
                )
            ),
            array(
                'created:2015-02-28T16:59:13Z',
                array(
                    'constraints' => array(
                        'created' => array(
                            'type' => 'datetime',
                        )
                    ),
                    'build_sql' => true,
                ),
                array(
                    'created' => array(
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array(date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z')),
                            )
                        ),
                        'field' => 'created',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.created = :t_created',
                                'parameter' => array('t_created' => date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z')),
                            )
                        ),
                    ),
                )
            ),
            array(
                'created:2015-02-28T16:59:13Z,2015-12-28T16:59:13Z',
                array(
                    'constraints' => array(
                        'created' => array(
                            'type' => 'datetime',
                        )
                    ),
                    'build_sql' => true,
                ),
                array(
                    'created' => array(
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array(
                                    date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z'),
                                    date_create_from_format('Y-m-d\TH:i:sP', '2015-12-28T16:59:13Z'),
                                ),
                            )
                        ),
                        'field' => 'created',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.created IN(:t_created)',
                                'parameter' => array('t_created' => [
                                    date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z'),
                                    date_create_from_format('Y-m-d\TH:i:sP', '2015-12-28T16:59:13Z'),
                                ]),
                            )
                        ),
                    ),
                )
            ),
            array(
                'created:>2015-02-28T16:59:13Z',
                array(
                    'constraints' => array(
                        'created' => array(
                            'type' => 'datetime',
                        )
                    ),
                    'build_sql' => true,
                ),
                array(
                    'created' => array(
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'gt',
                                'value' => date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z'),
                            )
                        ),
                        'field' => 'created',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.created > :t_created',
                                'parameter' => array('t_created' => date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z'),),
                            )
                        ),
                    ),
                )
            ),
            array(
                'created:<=2015-02-28T16:59:13Z,<2015-12-28T16:59:13Z',
                array(
                    'constraints' => array(
                        'created' => array(
                            'type' => 'datetime',
                        )
                    ),
                    'build_sql' => true,
                ),
                array(
                    'created' => array(
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'lte',
                                'value' => date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z'),
                            ),
                            array(
                                'condition' => 'lt',
                                'value' => date_create_from_format('Y-m-d\TH:i:sP', '2015-12-28T16:59:13Z'),
                            ),
                        ),
                        'field' => 'created',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.created <= :t_created',
                                'parameter' => array('t_created' => date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z'),),
                            ),
                            array(
                                'sql' => 't.created < :t_created2',
                                'parameter' => array('t_created2' => date_create_from_format('Y-m-d\TH:i:sP', '2015-12-28T16:59:13Z'),),
                            ),
                        ),
                    ),
                )
            ),
            array(
                'age:>=14;<20',
                array(
                    'constraints' => array(
                        'age' => array(
                            'type' => 'integer',
                        )
                    ),
                    'build_sql' => true,
                ),
                array(
                    'age' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'gte',
                                'value' => 14
                            ),
                            array(
                                'condition' => 'lt',
                                'value' => 20
                            ),
                        ),
                        'field' => 'age',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.age >= :t_age',
                                'parameter' => array('t_age' => 14,),
                            ),
                            array(
                                'sql' => 't.age < :t_age2',
                                'parameter' => array('t_age2' => 20,),
                            )
                        ),
                    ),
                )
            ),
        );
    }

    /**
     * @dataProvider providerSql
     * @param $filter
     * @param $options
     * @param $expected
     * @throws \QueryFilterSerializer\Filter\ParsingException
     */
    public function testSerializeSqlParts($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($this->genOptions($options));
        $serialization = $serializer->unserialize($filter);

        $this->assertEquals($expected, $serialization);
    }

    public function providerDateFrom()
    {
        return array(
            array(
                'date_from:2015-02-28T16:59:13Z',
                array('constraints' => array('date_from' => array('type' => 'datetime'))),
                array(
                    'date_from' => array(
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array(date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z')),
                            )
                        ),
                        'field' => 'date_from',
                    )
                )
            ),
            array(
                'date_from:!2015-02-28T16:59:13Z',
                array('constraints' => array('date_from' => array('type' => 'datetime'))),
                array(
                    'date_from' => array(
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'neq',
                                'value' => array(date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z')),
                            )
                        ),
                        'field' => 'date_from',
                    )
                )
            ),
            array(
                'date_from:2015-02-28T16:59:13Z,2015-05-05T00:00:00Z,,2015-04-28T16:59:13Z,',
                array('constraints' => array('date_from' => array('type' => 'datetime'))),
                array(
                    'date_from' => array(
                        'field' => 'date_from',
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array(
                                    date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z'),
                                    date_create_from_format('Y-m-d\TH:i:sP', '2015-05-05T00:00:00Z'),
                                    date_create_from_format('Y-m-d\TH:i:sP', '2015-04-28T16:59:13Z'),
                                ),
                            )
                        ),
                    )
                )
            ),
            array(
                'date_from:>2015-02-28T16:59:13Z',
                array('constraints' => array('date_from' => array('type' => 'datetime'))),
                array(
                    'date_from' => array(
                        'field' => 'date_from',
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'gt',
                                'value' => date_create_from_format('Y-m-d\TH:i:sP', '2015-02-28T16:59:13Z'),
                            )
                        ),
                    )
                )
            ),
            array(
                'date_from:<=2015-11-28T16:59:13Z',
                array('constraints' => array('date_from' => array('type' => 'datetime'))),
                array(
                    'date_from' => array(
                        'field' => 'date_from',
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'lte',
                                'value' => date_create_from_format('Y-m-d\TH:i:sP', '2015-11-28T16:59:13Z'),
                            )
                        ),
                    )
                )
            ),
            array(
                'date_from:<2015-11-28T16:59:13Z,<2015-08-28T16:59:13Z,<2015-9-28T16:59:13Z',
                array('constraints' => array('date_from' => array('type' => 'datetime'))),
                array(
                    'date_from' => array(
                        'field' => 'date_from',
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'lt',
                                'value' => date_create_from_format('Y-m-d\TH:i:sP', '2015-08-28T16:59:13Z'),
                            )
                        ),
                    )
                )
            ),
            array(
                'date_from:>2015-11-28T16:59:13Z,>2015-08-28T16:59:13Z,>2015-9-28T16:59:13Z',
                array('constraints' => array('date_from' => array('type' => 'datetime'))),
                array(
                    'date_from' => array(
                        'field' => 'date_from',
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'gt',
                                'value' => date_create_from_format('Y-m-d\TH:i:sP', '2015-11-28T16:59:13Z'),
                            )
                        ),
                    )
                )
            ),
            array(
                'date_from:>=2015-11-28T16:59:13Z,<2015-08-28T16:59:13Z,=2015-3-10T10:21:11Z,>2015-9-28T16:59:13Z',
                array('constraints' => array('date_from' => array('type' => 'datetime'))),
                array(
                    'date_from' => array(
                        'field' => 'date_from',
                        'type' => 'datetime',
                        'constraints' => array(
                            array(
                                'condition' => 'gte',
                                'value' => date_create_from_format('Y-m-d\TH:i:sP', '2015-11-28T16:59:13Z'),
                            ),
                            array(
                                'condition' => 'lt',
                                'value' => date_create_from_format('Y-m-d\TH:i:sP', '2015-08-28T16:59:13Z'),
                            ),
                            array(
                                'condition' => 'eq',
                                'value' => [date_create_from_format('Y-m-d\TH:i:sP', '2015-3-10T10:21:11Z')],
                            ),
                            array(
                                'condition' => 'gt',
                                'value' => date_create_from_format('Y-m-d\TH:i:sP', '2015-9-28T16:59:13Z'),
                            ),
                        ),
                    )
                )
            ),
            array(
                'date:2015-08-28T16:59:13Z',
                array('return_object' => true, 'constraints' => array('date' => array('type' => 'datetime'))),
                array(
                    'date' => new QueryFilterSerializer\Filter\FieldFilter(
                        array(
                            'type' => 'datetime',
                            'constraints' => array(
                                new QueryFilterSerializer\Filter\FieldConstraint(
                                    array(
                                        'condition' => 'eq',
                                        'value' => array(date_create_from_format('Y-m-d\TH:i:sP', '2015-08-28T16:59:13Z')),
                                    )
                                )
                            ),
                            'field' => 'date',
                        )
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider providerDateFrom
     * @param $filter
     * @param $options
     * @param $expected
     * @throws \QueryFilterSerializer\Filter\ParsingException
     */
    public function testDateTime($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($this->genOptions($options));
        $serialization = $serializer->unserialize($filter);

        $this->assertEquals($expected, $serialization);
    }

    public function providerEmbedded()
    {
        return array(
            array(
                'user:(id:12)',
                array(
                    'constraints' => array(
                        'user' => array(
                            'type' => 'embedded', 'options' => array(
                                'constraints' => array(
                                    'id' => array(
                                        'type' => 'integer'
                                    ),
                                ),
                            ),
                        )
                    ),
                    'build_sql' => true,
                ),
                array(
                    'user' => array(
                        'type' => 'embedded',
                        'constraints' => array(
                            'id' => array(
                                'type' => 'integer',
                                'constraints' => array(
                                    array(
                                        'condition' => 'eq',
                                        'value' => array('12'),
                                    )
                                ),
                                'field' => 'id',
                                'sql_parts' => array(
                                    array(
                                        'sql' => 't2.id = :t2_id',
                                        'parameter' => array('t2_id' => '12')
                                    ),
                                ),
                            )
                        ),
                        'field' => 'user',
                        'sql_parts' => array(
                            array(
                                'sql' => 't2.id = :t2_id',
                                'parameter' => array('t2_id' => '12')
                            ),
                        ),
                    )
                )
            ),
            array(
                'user:(name:John|status:active)',
                array(
                    'constraints' => array(
                        'user' => array(
                            'type' => 'embedded', 'options' => array(
                                'constraints' => array(
                                    'name' => array(
                                        'type' => 'string'
                                    ),
                                    'status' => array(
                                        'type' => 'string'
                                    ),
                                ),
                            )
                        )
                    )
                ),
                array(
                    'user' => array(
                        'type' => 'embedded',
                        'constraints' => array(
                            'name' => array(
                                'type' => 'string',
                                'constraints' => array(
                                    array(
                                        'condition' => 'eq',
                                        'value' => array('John'),
                                    )
                                ),
                                'field' => 'name',
                            ),
                            'status' => array(
                                'type' => 'string',
                                'constraints' => array(
                                    array(
                                        'condition' => 'eq',
                                        'value' => array('active'),
                                    )
                                ),
                                'field' => 'status',
                            ),
                        ),
                        'field' => 'user',
                    )
                )
            ),
            array(
                'user:(id:12|name:John)',
                array(
                    'constraints' => array(
                        'user' => array(
                            'type' => 'embedded', 'options' => array(
                                'constraints' => array(
                                    'id' => array(
                                        'type' => 'integer'
                                    ),
                                    'name' => array(
                                        'type' => 'string'
                                    ),
                                ),
                            )
                        )
                    )
                ),
                array(
                    'user' => array(
                        'type' => 'embedded',
                        'constraints' => array(
                            'id' => array(
                                'type' => 'integer',
                                'constraints' => array(
                                    array(
                                        'condition' => 'eq',
                                        'value' => array('12'),
                                    )
                                ),
                                'field' => 'id',
                            ),
                            'name' => array(
                                'type' => 'string',
                                'constraints' => array(
                                    array(
                                        'condition' => 'eq',
                                        'value' => array('John'),
                                    )
                                ),
                                'field' => 'name',
                            )
                        ),
                        'field' => 'user',
                    )
                )
            ),
            array(
                'id:10|user:(id:12|name:John)|name:Project',
                array(
                    'constraints' => array(
                        'user' => array(
                            'type' => 'embedded', 'options' => array(
                                'constraints' => array(
                                    'id' => array(
                                        'type' => 'integer'
                                    ),
                                    'name' => array(
                                        'type' => 'string'
                                    ),
                                ),
                                'table_name' => 'usr',
                            )
                        ),
                        'id' => array('type' => 'integer'),
                        'name' => array('type' => 'string'),
                    ),
                    'build_sql' => true,
                ),
                array(
                    'user' => array(
                        'type' => 'embedded',
                        'constraints' => array(
                            'id' => array(
                                'type' => 'integer',
                                'constraints' => array(
                                    array(
                                        'condition' => 'eq',
                                        'value' => array('12'),
                                    )
                                ),
                                'field' => 'id',
                                'sql_parts' => array(
                                    array(
                                        'sql' => 'usr.id = :usr_id',
                                        'parameter' => array('usr_id' => '12')
                                    ),
                                ),
                            ),
                            'name' => array(
                                'type' => 'string',
                                'constraints' => array(
                                    array(
                                        'condition' => 'eq',
                                        'value' => array('John'),
                                    )
                                ),
                                'field' => 'name',
                                'sql_parts' => array(
                                    array(
                                        'sql' => 'usr.name = :usr_name',
                                        'parameter' => array('usr_name' => 'John')
                                    ),
                                ),
                            )
                        ),
                        'field' => 'user',
                        'sql_parts' => array(
                            array(
                                'sql' => 'usr.id = :usr_id',
                                'parameter' => array('usr_id' => '12')
                            ),
                            array(
                                'sql' => 'usr.name = :usr_name',
                                'parameter' => array('usr_name' => 'John')
                            ),
                        ),
                    ),
                    'name' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('Project'),
                            )
                        ),
                        'field' => 'name',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.name = :t_name',
                                'parameter' => array('t_name' => 'Project'),
                            )
                        ),
                    ),
                    'id' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('10'),
                            )
                        ),
                        'field' => 'id',
                        'sql_parts' => array(
                            array(
                                'sql' => 't.id = :t_id',
                                'parameter' => array('t_id' => '10'),
                            )
                        ),
                    ),
                )
            ),
            // TODO: implement
//            array(
//                'user:(id:12|role:(name:Admin))',
//                array(
//                    'constraints' => array(
//                        'user' => array(
//                            'type' => 'embedded',
//                            'options' => array(
//                                'constraints' => array(
//                                    'id' => array(
//                                        'type' => 'integer'
//                                    ),
//                                    'role' => array(
//                                        'type' => 'embedded',
//                                        'options' => array(
//                                            'constraints' => array(
//                                                'name' => array('type' => 'string'),
//                                            )
//                                        )
//                                    ),
//                                ),
//                            )
//                        )
//                    )
//                ),
//                array(
//                    'user' => array(
//                        'type' => 'embedded',
//                        'constraints' => array(
//                            'id' => array(
//                                'type' => 'integer',
//                                'constraints' => array(
//                                    array(
//                                        'condition' => 'eq',
//                                        'value' => array('12'),
//                                    )
//                                ),
//                                'field' => 'id',
//                            )
//                        ),
//                        'field' => 'user',
//                    )
//                )
//            ),
        );
    }

    /**
     * @dataProvider providerEmbedded
     * @param $filter
     * @param $options
     * @param $expected
     * @throws \QueryFilterSerializer\Filter\ParsingException
     */
    public function testEmbedded($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($this->genOptions($options));
        $serialization = $serializer->unserialize($filter);

        $this->assertEquals($expected, $serialization);
    }

    /**
     * @param array $data
     * @return \QueryFilterSerializer\Filter\Config\Options
     */
    protected function genOptions(array $data)
    {
        $options = new \QueryFilterSerializer\Filter\Config\Options();
        $options->constraints = isset($data['constraints']) ? $data['constraints'] : [];

        if (isset($data['return_object'])) {
            $options->returnObject = $data['return_object'];
        }

        if (isset($data['build_sql'])) {
            $options->buildSql = $data['build_sql'];
        }

        return $options;
    }
}
