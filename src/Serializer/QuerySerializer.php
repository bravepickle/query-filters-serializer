<?php
/**
 * Date: 25.05.14
 * Time: 19:46
 */

namespace QueryFilterSerializer\Serializer;

use QueryFilterSerializer\Config\Options;
use QueryFilterSerializer\Encoder\EncoderInterface;
use QueryFilterSerializer\Encoder\StringEncoder;
use QueryFilterSerializer\Exception\FilterException;
use QueryFilterSerializer\Exception\ParsingException;
use QueryFilterSerializer\Filter\FieldFilter;
use QueryFilterSerializer\Filter\QueryFilterTypeInterface;
use QueryFilterSerializer\Loader\FilterTypeLoader;
use QueryFilterSerializer\Loader\LoaderInterface;

class QuerySerializer implements SerializerInterface
{
    /**
     * @var Options
     */
    protected $options;

    /**
     * @var array
     */
    protected $filterTypes = array();

    /**
     * @var EncoderInterface
     */
    public $encoder;

    /**
     * Filter type loaders
     * @var LoaderInterface[]
     */
    public $loaders = [];

    /**
     * QuerySerializer constructor.
     * @param Options|null $options
     * @param EncoderInterface|null $encoder
     */
    public function __construct(Options $options = null, EncoderInterface $encoder = null)
    {
        $this->options = $options ?: new Options();
        $this->encoder = $encoder ?: new StringEncoder();
        $this->registerDefaultLoaders();
    }

    /**
     * Register default loaders
     */
    public function registerDefaultLoaders()
    {
        $this->loaders[] = new FilterTypeLoader($this->options->constraintsNamespace);
    }

    /**
     * Get all available loaders
     * @return LoaderInterface[]
     */
    public function getLoaders()
    {
        return $this->loaders;
    }

    /**
     * Set loaders
     * @param LoaderInterface[] $loaders
     */
    public function setLoaders($loaders)
    {
        $this->loaders = $loaders;
    }

    /**
     * Add new loader
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * @return EncoderInterface
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @param EncoderInterface $encoder
     */
    public function setEncoder($encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param Options|null $options
     * @return $this
     */
    public function setOptions(Options $options = null)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Parse query into options list
     * @param $query
     * @return array
     * @throws ParsingException
     * @throws FilterException
     */
    public function unserialize($query)
    {
        if (!$query) {
            return array(); // no query parameters
        }

        $filterTypes = $this->encoder->decode($query, $this->genContext());

        $pairs = array();

        foreach ($filterTypes as $sub) {
            list($name, $value) = $sub;

            $modType = $this->options->constraintType;

            if (!isset($this->options->constraints[$name][$modType])) {
                throw new ParsingException('Failed to read options on query filter field: ' . $name);
            }

            list($typeSerializer, $parsed) = $this->parseFilterData($name, $modType, $value);

            if ($parsed) {
                $pairs = $this->initFieldFilterArr($pairs, $name, $parsed, $typeSerializer);
            }
        }

        if ($this->options->returnObject) {
            return $this->wrapArrayToConstraints($pairs);
        }

        return $pairs;
    }

    public function serialize(array $filters)
    {
        return $this->encoder->encode($filters, $this->genContext());
    }

    /**
     * @param $pairs
     * @return array
     */
    protected function wrapArrayToConstraints($pairs)
    {
        $objects = array();
        foreach ($pairs as $name => $arr) {
            $objects[$name] = new FieldFilter($arr);
        }

        return $objects;
    }

    /**
     * @param $pairs
     * @param $name
     * @param $parsed
     * @param QueryFilterTypeInterface $typeSerializer
     * @return mixed
     */
    protected function initFieldFilterArr($pairs, $name, $parsed, $typeSerializer)
    {
        $pairs[$name] = array();
        if (!isset($pairs[$name])) {
            $pairs[$name]['constraints'] = $parsed;
        } else {
            $pairs[$name]['constraints'] = array_merge($pairs[$name], $parsed);
        }
        $pairs[$name]['type'] = $typeSerializer->getName();

        // define field name by alias or leave as it is
        $pairs[$name]['field'] = isset($this->options->constraints[$name]['name']) ?
            $this->options->constraints[$name]['name'] : $name;

        if ($this->options->buildSql) { // build SQL parts?
            $pairs[$name][FieldFilter::KEY_SQL_PARTS] = $typeSerializer->buildSqlParts($pairs[$name], $this->options->tableName);

            // TODO: update FieldFilter objects to support DQLs!
        }

        return $pairs;
    }

    /**
     * @param $name
     * @param $modType
     * @param $value
     * @return array
     * @throws FilterException
     */
    protected function parseFilterData($name, $modType, $value)
    {
        $filterType = $this->getFilterTypeByName($this->options->constraints[$name][$modType]);

        $modOpt = $this->options->constraintOptions;

        if (isset($this->options->constraints[$name][$modOpt])) {
            $filterType->setOptions($this->options->constraints[$name][$modOpt]);
        }

        $parsed = $filterType->unserialize($value);

        return array($filterType, $parsed);
    }

    /**
     * @return array
     */
    protected function genContext()
    {
        $context = array();
        $context[EncoderInterface::CONTEXT_CONSTRAINTS] = $this->options->constraints;
        $context[EncoderInterface::CONTEXT_ENCODING] = $this->options->encoding;

        return $context;
    }

    /**
     * @param $name
     * @return null|QueryFilterTypeInterface|object
     * @throws FilterException
     */
    protected function getFilterTypeByName($name)
    {
        /** @var LoaderInterface $loader */
        foreach ($this->loaders as $loader) {
            if ($loader->supports($name)) {
                $object = $loader->load($name);

                // dependency injection
                if ($object instanceof QuerySerializerAwareInterface) {
                    $object->setSerializer($this);
                }

                return $object;
            }
        }

        throw new FilterException('Failed to find filter: ' . $name);
    }
}