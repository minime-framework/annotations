<?php

namespace Minime\Annotations\Fixtures;

/**
 * A Common DockBlock
 * 
 * @value bar
 */
class ReaderTraitFixture
{

    use \Minime\Annotations\Traits\Reader;

    /**
     * @value foo
     */
    private $property_fixture;


    /**
     * @value bar
     */
    private function method_fixture(){}

}