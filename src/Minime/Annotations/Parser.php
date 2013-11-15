<?php

namespace Minime\Annotations;

use Minime\Annotations\Interfaces\ParserInterface;
use Minime\Annotations\Interfaces\ParserRulesInterface;
use Minime\Annotations\Interfaces\CacheInterface;
use StrScan\StringScanner;

/**
 *
 * An Annotation Parser
 *
 * @package Annotations
 *
 */
class Parser implements ParserInterface
{
    /**
     * The ParserRules object
     * @var ParserRulesInterface
     */
    protected $rules;

    /**
     * The ParserRules object
     * @var CacheInterface
     */
    protected $cache;

    /**
     * The parsable type in a given docblock
     *
     * @var array
     */
    protected $types = ['string', 'integer', 'float', 'json', 'eval'];

    /**
     * Parser constructor
     * @param Annotations\Interfaces\ParserRulesInterface $rules
     */
    public function __construct(ParserRulesInterface $rules = null, CacheInterface $cache = null)
    {
        $this->rules = $rules;
        $this->cache = $cache;
    }

    /**
    * {@inheritdoc}
    */
    public function setRules(ParserRulesInterface $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
    * {@inheritdoc}
    */
    public function getRules()
    {
        return $this->rules;
    }

    /**
    * {@inheritdoc}
    */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
    * {@inheritdoc}
    */
    public function getCache()
    {
        return $this->cache;
    }

    /**
    * {@inheritdoc}
    */
    public function parse($str)
    {
        if (null === $this->cache) {
            return new AnnotationsBag($this->parseDocBlock($str), $this->getRules());
        }

        $key = $this->fetchCacheKey($str);
        if (! $this->cache->has($key)) {
            $this->cache->set($key, function () use ($str) {
                return new AnnotationsBag($this->parseDocBlock($str), $this->getRules());
            });
        }
        $bag = $this->cache->get($key);
        if (is_object($bag) && method_exists($bag, '__invoke')) {
            $annotation = $bag();
            if (! $annotation instanceof AnnotationsBag) {
                throw new ParserException('The cache is corrupted the return object is not a AnnotationsBag');
            }

            return $annotation;
        }
        throw new ParserException('The cache is corrupted the return value is not a AnnotationsBag object');
    }

    public function fetchCacheKey($str)
    {
        return md5(trim($str));
    }

    /**
     * parse a string
     *
     * @param string $docblock the doc block to parse
     *
     * @return array
     */
    protected function parseDocBlock($docblock)
    {
        $rules = $this->getRules();
        if (null === $rules) {
            throw new ParserException(
                'You must specify a ParserRules via the `setRules` method or the class constructor'
            );
        }
        $parameters = [];
        $identifier = $rules->getAnnotationIdentifier();
        $pattern = '/\\'.$identifier.$rules->getAnnotationNameRegex().'/';
        $typesPattern = '/('. implode('|', $this->types) .')/';
        array_walk(
            array_map("rtrim", explode("\n", $docblock)),
            function ($line) use (&$parameters, $identifier, $pattern, $typesPattern) {
                $line = new StringScanner($line);
                $line->skip('/\s+\*\s+/');
                while (! $line->hasTerminated()) {
                    $key = $line->scan($pattern);
                    if (! $key) { // next line when no annotation is found
                        $line->terminate();
                        continue;
                    }

                    $key = substr($key, strlen($identifier));
                    $line->skip('/\s+/');
                    if ('' == $line->peek() || $line->check('/\\'.$identifier.'/')) { // if implicit boolean
                        $parameters[$key] = true;
                        continue;
                    }

                    $type = 'dynamic';
                    if ($line->check($typesPattern)) { //if strong typed
                        $type = $line->scan('/\w+/');
                        $line->skip('/\s+/');
                    }
                    $parameters[$key][] = self::parseValue($line->getRemainder(), $type);
                }
            }
        );

        array_walk($parameters, function (&$value) {
            if (is_array($value) && 1 == count($value)) {
                $value = $value[0];
            }
        });

        return $parameters;
    }

    /**
     * Parse a given value against a specific type
     *
     * @param string $value
     * @param string $type  the type to parse the value against
     *
     * @throws ParserException If the type is not recognized
     *
     * @return scalar|object
     */
    protected static function parseValue($value, $type = 'string')
    {
        $method = 'parse'.ucfirst(strtolower($type));

        return self::{$method}($value);
    }

    /**
     * Parse a given undefined type value
     *
     * @param string $value
     *
     * @return scalar|object
     */
    protected static function parseDynamic($value)
    {
        $json = json_decode($value);
        if (JSON_ERROR_NONE == json_last_error()) {
            return $json;
        }

        return $value;
    }

    /**
     * Parse a given value
     *
     * @param string $value
     *
     * @return scalar|object
     */
    protected static function parseString($value)
    {
        return $value;
    }

    /**
     * Filter a value to be an Integer
     *
     * @param string $value
     *
     * @throws ParserException If $value is not an integer
     *
     * @return integer
     */
    protected static function parseInteger($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if (false === $value) {
            throw new ParserException("Raw value must be integer. Invalid value '{$value}' given.");
        }

        return $value;
    }

    /**
     * Filter a value to be a Float
     *
     * @param string $value
     *
     * @throws ParserException If $value is not a float
     *
     * @return float
     */
    protected static function parseFloat($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);
        if (false === $value) {
            throw new ParserException("Raw value must be float. Invalid value '{$value}' given.");
        }

        return $value;
    }

    /**
     * Filter a value to be a Json
     *
     * @param string $value
     *
     * @throws ParserException If $value is not a Json
     *
     * @return scalar|object
     */
    protected static function parseJson($value)
    {
        $json = json_decode($value);
        $error = json_last_error();
        if (JSON_ERROR_NONE != $error) {
            throw new ParserException("Raw value must be a valid JSON string. Invalid value '{$value}' given.");
        }

        return $json;
    }

    /**
     * Filter a value to be a PHP eval
     *
     * @param string $value
     *
     * @throws ParserException If $value is not a valid PHP code
     *
     * @return mixed
     */
    protected static function parseEval($value)
    {
        $output = @eval("return {$value};");
        if (false === $output) {
            throw new ParserException("Raw value should be valid PHP. Invalid code '{$value}' given.");
        }

        return $output;
    }
}
