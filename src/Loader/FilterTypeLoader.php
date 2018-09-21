<?php

namespace QueryFilterSerializer\Loader;


use QueryFilterSerializer\Exception\FilterException;
use QueryFilterSerializer\Filter\QueryFilterTypeInterface;

class FilterTypeLoader implements LoaderInterface
{
    const DEFAULT_NAMESPACE = 'QueryFilterSerializer\\Filter\\Type';

    /**
     * @var string namespace used for loading filters
     */
    protected $namespace;

    /**
     * FilterTypeLoader constructor.
     * @param $namespace
     */
    public function __construct($namespace = self::DEFAULT_NAMESPACE)
    {
        $this->namespace = $namespace;
    }

    /**
     * @param $name
     * @return bool
     */
    public function supports($name)
    {
        $fullClassName = $this->genClassName($name);

        return class_exists($fullClassName);
    }

    /**
     * @param string $name
     * @return null|object|QueryFilterTypeInterface
     * @throws FilterException
     */
    public function load($name)
    {
        $fullClassName = $this->genClassName($name);
        if (!class_exists($fullClassName)) {
            throw new FilterException('Failed to find filter with type: ' . $name);
        }

        /** @var QueryFilterTypeInterface $object */
        $object = new $fullClassName();

        if (!$object instanceof QueryFilterTypeInterface) {
            throw new FilterException(sprintf(
                'Filter "%s" must implement interface QueryFilterTypeInterface', get_class($object)));
        }

        return $object;
    }

    /**
     * @param $name
     * @return string
     */
    protected function genClassName($name)
    {
        $fullClassName = $this->namespace . '\\' . ucwords($name) . 'Type';

        return $fullClassName;
    }
}