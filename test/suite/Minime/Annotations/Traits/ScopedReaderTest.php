<?php

namespace Minime\Annotations\Traits;

use \Minime\Annotations\Fixtures\ScopedReaderTraitFixture;

class ScopedReaderTest extends \PHPUnit_Framework_TestCase
{

    private $Fixture;

    public function setUp()
    {
        $this->Fixture = new ScopedReaderTraitFixture;
    }

    public function tearDown()
    {
        $this->Fixture = null;
    }

    public function testTraitReadsAnnotationsFromClass()
    {
        $this->assertSame('bar', $this->Fixture->getClassAnnotations()->get('value'));
    }

    public function testTraitReadsAnnotationsFromProperty()
    {
        $this->assertSame('foo', $this->Fixture->getPropertyAnnotations('property_fixture')->get('value'));
    }

    public function testTraitReadsAnnotationsFromMethod()
    {
        $this->assertSame('bar', $this->Fixture->getMethodAnnotations('method_fixture')->get('value'));
    }

}