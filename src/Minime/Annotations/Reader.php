<?php

namespace Minime\Annotations;

use \ReflectionClass;
use \ReflectionProperty;
use \ReflectionMethod;

class Reader
{

    protected $Parser;

    public function __construct(Parser $Parser = null)
    {
        ($Parser) ? $this->Parser = $Parser : $this->Parser = new Parser();
    }

    /**
     * Retrieve all annotations from a given class
     * 
     * @param  string $class Full qualified class name
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     * @throws  \ReflectionException If class is not found
     */
    public function getClassAnnotations($class)
    {
        $reflection = new ReflectionClass($class);
        return $this->createAnnotationsBag($reflection);
    }

    /**
     * Retrieve all annotations from a given property of a class
     * 
     * @param  string $class Full qualified class name
     * @param  string $property Property name
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     * @throws  \ReflectionException If property is undefined
     */
    public function getPropertyAnnotations($class, $property)
    {
        $reflection = new ReflectionProperty($class, $property);
        return $this->createAnnotationsBag($reflection);
    }

    /**
     * Retrieve all annotations from a given method of a class
     * 
     * @param  string $class Full qualified class name
     * @param  string $property Method name
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     * @throws  \ReflectionException If method is undefined
     */
    public function getMethodAnnotations($class, $method)
    {
        $reflection = new ReflectionMethod($class, $method);
        return $this->createAnnotationsBag($reflection);
    }

    protected function createAnnotationsBag($reflection)
    {
        $Parser = $this->Parser;
        $annotations = $Parser->parse($reflection->getDocComment());
        return new AnnotationsBag($annotations);
    }

}
