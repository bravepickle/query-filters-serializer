<?php
/**
 * Date: 21.09.18
 * Time: 16:32
 */

namespace QueryFilterSerializer\Loader;


interface LoaderInterface
{
    /**
     * @param string $name class name or its alias
     * @return object|null
     */
    public function load($name);

    /**
     * Return true if this loader can process this class
     * @param string $name
     * @return boolean
     */
    public function supports($name);
}