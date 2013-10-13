<?php

namespace Minime\Annotations\Traits;

trait Reader
{

    /**
     * Retrieve all annotations from a given class
     * 
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     */
    public function getClassAnnotations($class)
    {
        return $this->getAnnotationsReader()->getClassAnnotations($class, $this);
    }

    /**
     * Retrieve all annotations from a given property of a given class
     * 
     * @param  string $property Property name
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     */
    public function getPropertyAnnotations($class, $property)
    {
        return $this->getAnnotationsReader()->getPropertyAnnotations($class, $property);
    }

    /**
     * Retrieve all annotations from a given method of a given class
     * 
     * @param  string $property Method name
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     */
    public function getMethodAnnotations($class, $method)
    {
        return $this->getAnnotationsReader()->getMethodAnnotations($class, $method);
    }

    /**
     * Override this method if you want to use a custom Parser
     * 
     * @return \Minime\Annotations\Parser
     */
    public function getAnnotationsParser()
    {
        return new \Minime\Annotations\Parser();
    }

    /**
     * Override this method if you want to use a custom Reader
     * 
     * @return \Minime\Annotations\Reader
     */
    protected function getAnnotationsReader()
    {
        return new \Minime\Annotations\Reader( $this->getAnnotationsParser() );
    }

}
