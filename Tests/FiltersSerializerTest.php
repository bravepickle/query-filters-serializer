<?php
require_once __DIR__ . '/../vendor/autoload.php';

use \Filter\QuerySerializer;

class FiltersSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function providerAge()
    {
        return array(
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
                'age:!=18',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'ne',
                                'value' => array('18'),
                            )
                        ),
                        'field' => 'age',
                    )
                )
            ),
            array(
                'age:18,20,21,20,,0',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'field' => 'age',
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('18', '20', '21'),
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
                'age:<23,<2,<10',
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
                'age:>4,>20,>7',
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
                'age:>=14,<18,16,=17',
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
                    'age' => new \Filter\FieldFilter(
                        array(
                            'type' => 'integer',
                            'constraints' => array(
                                new \Filter\FieldConstraint(
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
     */
    public function testSerializeAge($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($options);
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
     */
    public function testSerializeName($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($options);
        $serialization = $serializer->unserialize($filter);

        $this->assertEquals($expected, $serialization);
    }


    public function providerComposite()
    {
        return array(
            array(
                'email:user@example.com|id:<10,>=2',
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
     */
    public function testSerializeComposite($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($options);
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
     */
    public function testSerializeBool($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($options);
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
     */
    public function testSerializeEnum($filter, $options, $expected)
    {
        $serializer = new QuerySerializer();
        $serializer->setOptions($options);
        $serialization = $serializer->unserialize($filter);

        $this->assertEquals($expected, $serialization);
    }

}
