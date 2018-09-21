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

    /**
     * @dataProvider providerAge
     * @dataProvider providerName
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
    public function testSerializerArray($filter, $options, $expected)
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
    protected function runSerializer($filter, $options, $expected): void
    {
        $serialization = $this->unserialize($filter, $options);

        $this->assertEquals($expected, $serialization);
    }

    /**
     * @param $filter
     * @param $options
     * @return array
     * @throws App\Exception\FilterException
     * @throws App\Exception\ParsingException
     */
    protected function unserialize($filter, $options): array
    {
        $encoder = new App\Encoder\UrlQueryEncoder();
        $serializer = new QuerySerializer($this->genOptions($options), $encoder);
        $serialization = $serializer->unserialize($filter);

        return $serialization;
    }
}
