<?php

namespace Minime\Annotations;

use Minime\Annotations\Interfaces\CacheInterface;
use DateTime;
use DateInterval;

/**
 *
 * An Annotation ParserCache
 *
 * @package Annotations
 *
 */
class ArrayCache implements CacheInterface
{

    /**
     * The Cache container
     * @var array
     */
    private $data = [];

    /**
     * The Cache Options
     * @var array
     */
    private $options = ['ttl' => '1 HOUR', 'limit' => 100];

    /**
     * The constructor
     * @param string  $ttl   the relative formats supported by the parser used for strtotime() and DateTime
     * @param integer $limit the maximum number of items the cache can contain
     */
    public function __construct($ttl = null, $limit = null)
    {
        if (null == $ttl) {
            $ttl = $this->options['ttl'];
        }
        $this->setOption('ttl', $ttl);
        if (null != $limit) {
            $this->setLimit($limit);
        }
    }

    /**
    * {@inheritdoc}
    */
    public function has($key)
    {
        $this->clear();

        return array_key_exists($key, $this->data);
    }

    /**
    * {@inheritdoc}
    */
    public function get($key)
    {
        if (! $this->has($key)) {
            return null;
        }

        return $this->data[$key]['value'];
    }

    /**
    * {@inheritdoc}
    */
    public function set($key, $value, $ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->options['ttl'];
        }
        if (! $ttl instanceof DateInterval) {
            $ttl = DateInterval::createFromDateString($ttl);
        }
        $this->data[$key] = ['ttl' => (new DateTime)->add($ttl), 'value' => $value];

        return $this;
    }

    /**
    * {@inheritdoc}
    */
    public function remove($key)
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
    * {@inheritdoc}
    */
    public function clear()
    {
        $now = new DateTime;
        $this->data = array_slice(array_filter($this->data, function ($value) use ($now) {
            return $now < $value['ttl'];
        }), 0, $this->options['limit'], true);

        return $this;
    }

    /**
    * {@inheritdoc}
    */
    public function reset()
    {
        $this->data = [];

        return $this;
    }

    /**
    * {@inheritdoc}
    */
    public function setOption($key, $value)
    {
        if ('limit' == $key) {
            return $this->setLimit($value);
        } elseif ('ttl' == $key) {
            $value = DateInterval::createFromDateString($value);
        }
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * Set the maximum number of items the cache can contain
     * @param interger $value
     *
     * @throws InvalidArgumentException If $value is not a valid positif integer
     *
     * @return self
     */
    public function setLimit($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_INT, array(
            'flags' => FILTER_REQUIRE_SCALAR,
            'options' => array('min_range' => 1)
        ));
        if (false === $value) {
            throw new \InvalidArgumentException('limit must be a valid positif integer');
        }
        $this->options['limit'] = $value;

        return $this;
    }
}
