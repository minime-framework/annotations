<?php

namespace Minime\Annotations;

use \Minime\Annotations\Fixtures\AnnotationsFixture;
use \ReflectionProperty;

class ParserTest extends \PHPUnit_Framework_TestCase
{

    private $Fixture;

    public function setUp()
    {
        $this->Fixture = new AnnotationsFixture;
    }

    protected function getAnnotationsFromFixture($fixture_name)
    {
        $reflection = new ReflectionProperty($this->Fixture, $fixture_name);
        return (new Parser())->parse($reflection->getDocComment());
    }

    /**
     * @test
     */
    public function parseEmptyFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('empty_fixture');
        $this->assertSame([], $annotations);
    }

    /**
     * @test
     */
    public function parseNullFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('null_fixture');
        $this->assertSame([null, null, ''], $annotations['value']);
    }

    /**
     * @test
     */
    public function parseBooleanFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('boolean_fixture');
        $this->assertSame([true, false, true, false, "true", "false"], $annotations['value']);
    }

    /**
     * @test
     */
    public function parseImplicitBooleanFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('implicit_boolean_fixture');
        $this->assertSame(TRUE, $annotations['alpha']);
        $this->assertSame(TRUE, $annotations['beta']);
        $this->assertSame(TRUE, $annotations['gamma']);
        $this->assertArrayNotHasKey('delta', $annotations);
    }

    /**
     * @test
     */
    public function parseStringFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('string_fixture');
        $this->assertSame(['abc', 'abc', '123'], $annotations['value']);
    }

    /**
     * @test
     */
    public function parseIntegerFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('integer_fixture');
        $this->assertSame([123, 23, -23], $annotations['value']);
    }

    /**
     * @test
     */
    public function parseFloatFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('float_fixture');
        $this->assertSame([.45, 0.45, 45., -4.5], $annotations['value']);
    }

    /**
     * @test
     */
    public function parseJsonFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('json_fixture');
        $this->assertEquals(
            [
                ["x", "y"],
                json_decode('{"x": {"y": "z"}}'),
                json_decode('{"x": {"y": ["z", "p"]}}')
            ],
        $annotations['value']);
    }

    /**
     * @test
     */
    public function parseSingleValuesFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('single_values_fixture');
        $this->assertEquals('foo', $annotations['param_a']);
        $this->assertEquals('bar', $annotations['param_b']);
    }

    /**
     * @test
     */
    public function parseMultipleValuesFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('multiple_values_fixture');
        $this->assertEquals(['x','y','z'], $annotations['value']);
    }

    /**
     * @test
     */
    public function parseParseSameLineFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('same_line_fixture');
        $this->assertSame(TRUE, $annotations['get']);
        $this->assertSame(TRUE, $annotations['post']);
        $this->assertSame(TRUE, $annotations['ajax']);
        $this->assertSame(TRUE, $annotations['alpha']);
        $this->assertSame(TRUE, $annotations['beta']);
        $this->assertSame(TRUE, $annotations['gamma']);
        $this->assertArrayNotHasKey('undefined', $annotations);
    }

    /**
     * @test
     */
    public function namespacedAnnotations()
    {
        $annotations = $this->getAnnotationsFromFixture('namespaced_fixture');
        
        $this->assertSame('cheers!', $annotations['path.to.the.treasure']);
        $this->assertSame('the cake is a lie', $annotations['path.to.the.cake']);
        $this->assertSame('foo', $annotations['another.path.to.cake']);
    }

    /**
     * @test
     */
    public function parseStrongTypedFixture()
    {
        $annotations = $this->getAnnotationsFromFixture('strong_typed_fixture');
        $declarations = $annotations['value'];
        $this->assertNotEmpty($declarations);
        $this->assertSame(
            [
                "abc", "45", // string
                45, -45, // integer
                .45, 0.45, 45.0, -4.5, 4., // float
            ],
            $declarations
        );

        $declarations = $annotations['json_value'];
        $this->assertEquals(
            [
                ["x", "y"], // json
                json_decode('{"x": {"y": "z"}}'),
                json_decode('{"x": {"y": ["z", "p"]}}')
            ],
            $declarations
        );
    }

    /**
     * @test
     * @expectedException Minime\Annotations\ParserException
     */
    public function badJSONValue()
    {
        $annotations = $this->getAnnotationsFromFixture('bad_json_fixture');
    }

    /**
     * @test
     */
    public function tolerateUnrecognizedTypes()
    {
        $annotations = $this->getAnnotationsFromFixture('non_recognized_type_fixture');
        $this->assertEquals("footype Tolerate me. DockBlocks can't be evaluated rigidly.", $annotations['value']);
    }

    /**
     * @test
     * @expectedException Minime\Annotations\ParserException
     */
    public function badIntegerValue()
    {
        $annotations = $this->getAnnotationsFromFixture('bad_integer_fixture');
    }

    /**
     * @test
     * @expectedException Minime\Annotations\ParserException
     */
    public function badFloatValue()
    {
        $annotations = $this->getAnnotationsFromFixture('bad_float_fixture');
    }

}