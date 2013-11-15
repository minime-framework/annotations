<?php

namespace Minime\Annotations\Interfaces;

/**
 *
 * Interface for Parser
 *
 * @package Annotations
 *
 */
interface ParserInterface
{
    /**
     * uses the parserRules object to parse the given string
     *
     * @throws Annotations\Exceptions\ParserException If the parsing could not be done
     *
     * @return Annotations\AnnotationsBag
     *
     */
    public function parse($str);

    /**
     * set a ParserRules
     *
     * @param Annotations\Interfaces\ParserRulesInterface $rules
     *
     * @return self
     */
    public function setRules(ParserRulesInterface $rules);

    /**
     * get the current available ParserRule
     *
     * @throws Annotations\Exceptions\ParserException If no parserRules is found
     *
     * @return Annotations\Interfaces\ParserRulesInterface
     */
    public function getRules();

    /**
     * set a cache object
     *
     * @param Annotations\Interfaces\CacheInterface $cache
     *
     * @return self
     */
    public function setCache(CacheInterface $cache);

    /**
     * get the current available cache
     *
     * @throws Annotations\Exceptions\ParserException If no cache is found
     *
     * @return Annotations\Interfaces\CacheInterface
     */
    public function getCache();
}
