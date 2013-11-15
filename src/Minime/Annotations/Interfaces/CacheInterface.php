<?php

namespace Minime\Annotations\Interfaces;

/**
 *
 * Interface for Parser
 *
 * @package Annotations
 *
 */
interface CacheInterface
{
    /**
     * Tell if a value exist and is not obsolete in the cache for the given key
     *
     * @param string $key
     *
     * @return boolean
     */
    public function has($key);

    /**
     * Retrieve the cached value for the given key
     * if no key is found or is obsolete return null
     *
     * @param string $key
     *
     * @return mixed|null
     */
    public function get($key);

    /**
     * Add/Replace a value to the cache using a given key
     *
     * @param string $key
     * @param mixed  $value
     * @param string $ttl
     *
     * @return self
     */
    public function set($key, $value, $ttl = null);

    /**
     * remove a value from the cache using its key
     *
     * @param string $key
     *
     * @return self
     */
    public function remove($key);

    /**
     * remove all expired item from the cache
     *
     * @return self
     */
    public function clear();

    /**
     * reset the cache by clearing it
     *
     * @return self
     */
    public function reset();

    /**
     * Set option for the cache
     *
     * @param string $key   option name
     * @param mixed  $value option value
     */
    public function setOption($key, $value);
}
