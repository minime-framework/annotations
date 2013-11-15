<?php

namespace Minime\Annotations;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 *
 * A facade for annotations parsing
 *
 * @package Annotations
 *
 */
class Facade
{
    /**
     * Retrieve all annotations from a given class
     *
     * @param  string                            $class Full qualified class name
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     * @throws \\ReflectionException             If class is not found
     */
    public static function getClassAnnotations($class)
    {
        return (new Parser(new ParserRules))
            ->parse((new ReflectionClass($class))->getDocComment());
    }

    /**
     * Retrieve all annotations from a given property of a class
     *
     * @param  string                            $class    Full qualified class name
     * @param  string                            $property Property name
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     * @throws \\ReflectionException             If property is undefined
     */
    public static function getPropertyAnnotations($class, $property)
    {
        return (new Parser(new ParserRules))
            ->parse((new ReflectionProperty($class, $property))->getDocComment());
    }

    /**
     * Retrieve all annotations from a given method of a class
     *
     * @param  string                            $class    Full qualified class name
     * @param  string                            $property Method name
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     * @throws \\ReflectionException             If method is undefined
     */
    public static function getMethodAnnotations($class, $method)
    {
        return (new Parser(new ParserRules))
            ->parse((new ReflectionMethod($class, $method))->getDocComment());
    }
}
