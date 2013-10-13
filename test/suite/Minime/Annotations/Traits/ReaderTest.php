<?php

namespace Minime\Annotations\Traits;

use \Minime\Annotations\Fixtures\ReaderTraitFixture;

class ReaderTest extends \PHPUnit_Framework_TestCase
{

    private $Fixture;

    public function setUp()
    {
        $this->Fixture = new ReaderTraitFixture;
    }

    public function tearDown()
    {
        $this->Fixture = null;
    }

    public function testTraitReadsAnnotationsFromClass()
    {
        $annotations = $this->Fixture->getClassAnnotations('\Minime\Annotations\Fixtures\ReaderTraitFixture');
        $this->assertSame('bar', $annotations->get('value'));
    }

    public function testTraitReadsAnnotationsFromProperty()
    {
        $annotations = $this->Fixture->getPropertyAnnotations('\Minime\Annotations\Fixtures\ReaderTraitFixture', 'property_fixture');
        $this->assertSame('foo', $annotations->get('value'));
    }

    public function testTraitReadsAnnotationsFromMethod()
    {
        $annotations = $this->Fixture->getMethodAnnotations('\Minime\Annotations\Fixtures\ReaderTraitFixture', 'method_fixture');
        $this->assertSame('bar', $annotations->get('value'));
    }

}