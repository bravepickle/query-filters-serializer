<?php

namespace QueryFilterSerializerTest;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use QueryFilterSerializer\Serializer\QuerySerializer;
use QueryFilterSerializer as App;

class UrlQueryEncoderTest extends TestCase
{
    public function providerAge()
    {
        return array(
            array(
                '_[age]=18',
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
                '_[age]=!18',
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
                '_[age]=18;20',
                array('constraints' => array('age' => array('type' => 'integer', 'multiple' => true))),
                array(
                    'age' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array(18, 20),
                            )
                        ),
                        'field' => 'age',
                    )
                )
            ),
            array(
                '_[age][]=18&_[age][]=&_[age][]=20',
                array('constraints' => array('age' => array('type' => 'integer'))),
                array(
                    'age' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array(18, 20),
                            )
                        ),
                        'field' => 'age',
                    )
                )
            ),
        );
    }

    public function providerCustom()
    {
        return array(
            array(
                ['calls' => 23, 'search' => 'base'],
                array('constraints' => array('calls' => array('type' => 'integer'), 'search' => ['type' => 'string'])),
                array(
                    'calls' => array(
                        'type' => 'integer',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array(23),
                            )
                        ),
                        'field' => 'calls',
                    ),
                    'search' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'eq',
                                'value' => array('base'),
                            )
                        ),
                        'field' => 'search',
                    )
                )
            ),
        );
    }

    public function providerName()
    {
        return array(
            array(
                '_[name]=John, hello',
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
                '_[name]=John;hello;!Jake;Jim!',
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
                '_[name][]=John&_[name][]=hello&_[name][]=!Jake&_[name][]=Jim!',
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
                '_[name]="I hope; this: will, wo|rk',
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
                '_[first_name]=!not',
                array(
                    'constraints' => array(
                        'first_name' => array(
                            'type' => 'string',
                            'options' => array('use_not' => true)
                        )
                    )
                ),
                array(
                    'first_name' => array(
                        'type' => 'string',
                        'constraints' => array(
                            array(
                                'condition' => 'neq',
                                'value' => array('not'),
                            )
                        ),
                        'field' => 'first_name',
                    )
                ),
            ),
            array(
                '_[status]=active',
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

    public function providerArray()
    {
        return array(
            array(
                '_[active][]=true',
                array('constraints' => array('active' => array('type' => 'boolean',))),
                App\Exception\ArrayMaxDepthException::class
            ),
            array(
                '_[active][][]=true',
                array('constraints' => array('active' => array('type' => 'string',))),
                App\Exception\ArrayMaxDepthException::class
            ),
            array(
                '_[name][sub][]=true',
                array('constraints' => array('name' => array('type' => 'string',))),
                App\Exception\ArrayMaxDepthException::class
            ),
            array(
                '_[foo][]=value',
                array('constraints' => array('foo' => array('type' => 'boolean',))),
                App\Exception\ArrayMaxDepthException::class
            ),
            array(
                '_[foo][]=value',
                array('constraints' => array('foo' => array('type' => 'datetime',))),
                App\Exception\ArrayMaxDepthException::class
            ),
            array(
                '_[foo][]=value',
                array('constraints' => array('foo' => array('type' => 'date',))),
                App\Exception\ArrayMaxDepthException::class
            ),
            array(
                '_[foo][bar][]=value',
                array('constraints' => array('foo' => array('type' => 'enum',))),
                App\Exception\ArrayMaxDepthException::class
            ),
            array(
                '_[foo][bar][]=value',
                array('constraints' => array('foo' => array('type' => 'integer',))),
                App\Exception\ArrayMaxDepthException::class
            ),
        );
    }


    public function providerEmbedded()
    {
        return array(
            array(
                '_[user][id]=12',
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
                '_[user][name]=John&_[user][status]=active',
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
                '_[user][id]=12&_[user][name]=John',
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
                '_[id]=10&_[user][id]=12&&_[user][name]=John&_[name]=Project',
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
            array(
                '_[user][id]=12&_[user][role][name]=Admin',
                array(
                    'constraints' => array(
                        'user' => array(
                            'type' => 'embedded',
                            'options' => array(
                                'constraints' => array(
                                    'id' => array(
                                        'type' => 'integer'
                                    ),
                                    'role' => array(
                                        'type' => 'embedded',
                                        'options' => array(
                                            'constraints' => array(
                                                'name' => array('type' => 'string'),
                                            )
                                        )
                                    ),
                                ),
                            )
                        )
                    )
                ),
                array(
                    'user' => array(
                        'constraints' => array(
                            'id' => array(
                                'constraints' => array(
                                    array(
                                        'condition' => 'eq',
                                        'value' => array(12),
                                    ),
                                ),
                                'type' => 'integer',
                                'field' => 'id',
                            ),
                            'role' => array(
                                    'constraints' => array(
                                        'name' => array(
                                            'constraints' => array(
                                                array(
                                                    'condition' => 'eq',
                                                    'value' => array('Admin'),
                                                ),
                                            ),
                                            'type' => 'string',
                                            'field' => 'name',
                                        ),
                                    ),
                                    'type' => 'embedded',
                                    'field' => 'role',
                                ),
                            ),
                        'type' => 'embedded',
                        'field' => 'user',
                    ),
                )
            ),
        );
    }

    /**
     * @dataProvider providerAge
     * @dataProvider providerName
     * @dataProvider providerEmbedded
     * @dataProvider providerCustom
     * @param $filter
     * @param $options
     * @param $expected
     * @throws App\Exception\ParsingException
     * @throws App\Exception\FilterException
     */
    public function testSerializeBase($filter, $options, $expected)
    {
        $this->runSerializer($filter, $options, $expected);
    }

    /**
     * @dataProvider providerArray
     * @param $filter
     * @param $options
     * @param $expected
     * @throws App\Exception\ParsingException
     * @throws App\Exception\FilterException
     */
    public function testSerializerArrayMaxDepth($filter, $options, $expected)
    {
        $this->expectException($expected);

        $this->unserialize($filter, $options);
    }

    /**
     * @param array $data
     * @return App\Config\Options
     */
    protected function genOptions(array $data)
    {
        $options = new App\Config\Options();
        $options->constraints = isset($data['constraints']) ? $data['constraints'] : [];

        if (isset($data['return_object'])) {
            $options->returnObject = $data['return_object'];
        }

        if (isset($data['build_sql'])) {
            $options->buildSql = $data['build_sql'];
        }

        return $options;
    }

    /**
     * @param $filter
     * @param $options
     * @param $expected
     * @throws App\Exception\FilterException
     * @throws App\Exception\ParsingException
     */
    protected function runSerializer($filter, $options, $expected)
    {
        $actual = $this->unserialize($filter, $options);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @param $filter
     * @param $options
     * @return array
     * @throws App\Exception\FilterException
     * @throws App\Exception\ParsingException
     */
    protected function unserialize($filter, $options)
    {
        $encoder = new App\Encoder\UrlQueryEncoder();
        $options = $this->genOptions($options);
        $options->filterTypeEncoders[App\Filter\Type\EmbeddedType::NAME] =
            App\Encoder\Filter\ArrayEmbeddedTypeEncoder::class;
        $serializer = new QuerySerializer($options, $encoder);
        $serialization = $serializer->unserialize($filter);

        return $serialization;
    }

    /**
     * @throws App\Exception\FilterException
     * @throws App\Exception\ParsingException
     */
    public function testExample()
    {
        $options = new App\Config\Options();
        $options->constraints = array('age' => array('type' => 'integer'));
        $filterQuery = '_[age][]=>=14&_[age][]=<18'; // string to parse
        $filterQueryAlt = ["age" => [">=14", "<18"]];  // this is an alternative way to specify filters. Array used instead

        $encoder = new App\Encoder\UrlQueryEncoder();
        $options->filterTypeEncoders[App\Filter\Type\EmbeddedType::NAME] =
            App\Encoder\Filter\ArrayEmbeddedTypeEncoder::class;
        $serializer = new QuerySerializer($options, $encoder);
        $filters = $serializer->unserialize($filterQuery);
        $filtersAlt = $serializer->unserialize($filterQueryAlt);

        $expected = array (
            'age' => array (
                'constraints' => array (
                    array (
                        'condition' => 'gte',
                        'value' => '14',
                    ),
                    array (
                        'condition' => 'lt',
                        'value' => '18',
                    ),
                ),
                'type' => 'integer',
                'field' => 'age',
            ),
        );

        $this->assertEquals($expected, $filters);
        $this->assertEquals($expected, $filtersAlt);
    }
}
