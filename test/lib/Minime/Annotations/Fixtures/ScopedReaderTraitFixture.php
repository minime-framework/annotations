<?php

namespace Minime\Annotations\Fixtures;

/**
 * A Common DockBlock
 * 
 * @value bar
 */
class ScopedReaderTraitFixture
{

    use \Minime\Annotations\Traits\ScopedReader;

    /**
     * @value foo
     */
    private $property_fixture;


    /**
     * @value bar
     */
    private function method_fixture(){}

}