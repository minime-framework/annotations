<?php

namespace Minime\Annotations\Traits;

trait ScopedReader
{

    use Reader;

    /**
     * Retrieve all annotations from current class
     * 
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     */
    public function getClassAnnotations()
    {
        return $this->getAnnotationsReader()->getClassAnnotations($this);
    }

    /**
     * Retrieve all annotations from a given property of current class
     * 
     * @param  string $property Property name
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     */
    public function getPropertyAnnotations($property)
    {
        return $this->getAnnotationsReader()->getPropertyAnnotations($this, $property);
    }

    /**
     * Retrieve all annotations from a given method of current class
     * 
     * @param  string $property Method name
     * @return Minime\Annotations\AnnotationsBag Annotations collection
     */
    public function getMethodAnnotations($method)
    {
        return $this->getAnnotationsReader()->getMethodAnnotations($this, $method);
    }

}
