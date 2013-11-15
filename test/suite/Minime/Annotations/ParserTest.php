<?php

namespace Minime\Annotations;

use \Minime\Annotations\Fixtures\AnnotationsFixture;
use \ReflectionProperty;

class ParserTest extends \PHPUnit_Framework_TestCase
{

    private $Fixture;

    private $rules;

    public function setUp()
    {
        $this->rules = new ParserRules;
        $this->Fixture = new AnnotationsFixture;
        $this->cache = new ArrayCache;
    }

    /**
     * @test
     * @expectedException PHPUnit_Framework_Error
     */
    public function testParserRequiredAParserRules()
    {
        new Parser('hellow world!');
    }

    public function testParseEmptyFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'empty_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertSame([], $annotations->export());
    }

    public function testRulesSetterAndGetter()
    {
        $parser = new Parser;
        $parser->setRules($this->rules);
        $rules = $parser->getRules();
        $this->assertSame($rules, $this->rules);
    }

    public function testNotParserSet()
    {
        $rules = (new Parser)->getRules();
        $this->assertNull($rules);
    }

    public function testCacheSetterAndGetter()
    {
        $parser = new Parser;
        $this->assertSame($parser->setCache($this->cache)->getCache(), $this->cache);
    }

    public function testNotCacheSet()
    {
        $cache = (new Parser)->getCache();
        $this->assertNull($cache);
    }

    /**
     * @expectedException Minime\Annotations\ParserException
     */
    public function testBadParsingWithoutRules()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'null_fixture');
        (new Parser)->parse($reflection->getDocComment());
    }

    public function testParseNullFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'null_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertSame([null, null, ''], $annotations->get('value'));
    }

    public function testParseWithCache()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'null_fixture');
        $docblock = $reflection->getDocComment();
        $cache = new ArrayCache('10 MINUTES', 50);
        $parser = new Parser($this->rules, $cache);
        $parser
            ->getCache()
            ->setOption('ttl', '+2 MINUTES')
            ->setOption('limit', 50)
            ->reset(); //make sure the cache is empty

        $annotations1 = $parser->parse($docblock);
        $annotations2 = $parser->parse($docblock);
        $annotations3 = $parser->getCache()->get($parser->fetchCachekey($docblock));
        $this->assertSame($annotations1->export(), $annotations2->export());
        $this->assertSame($annotations2->export(), $annotations3()->export());
        $parser->getCache()->remove($parser->fetchCachekey($docblock));
        $this->assertNull($parser->getCache()->get($docblock));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongCacheLimit()
    {

        $this->cache->setOption('limit', -10);
    }
    /**
     * @expectedException Minime\Annotations\ParserException
     */
    public function testParseCacheError()
    {
        $parser = new Parser($this->rules, $this->cache);
        $this->cache->set($parser->fetchCachekey('foo'), 'bar', '1 MINUTE');
        $parser->parse('foo');
    }

    /**
     * @expectedException Minime\Annotations\ParserException
     */
    public function testParseCacheError2()
    {
        $parser = new Parser($this->rules, $this->cache);
        $this->cache->set($parser->fetchCachekey('foo'), function () {
            return 'bar';
        });
        (new Parser($this->rules, $this->cache))->parse('foo');
    }

    public function testParseBooleanFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'boolean_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertSame([true, false, true, false, "true", "false"], $annotations->get('value'));
    }

    public function testParseImplicitBooleanFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'implicit_boolean_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertSame(true, $annotations->get('alpha'));
        $this->assertSame(true, $annotations->get('beta'));
        $this->assertSame(true, $annotations->get('gamma'));
        $this->assertSame(null, $annotations->get('delta'));
    }

    public function testParseStringFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'string_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertSame(['abc', 'abc', '123'], $annotations->get('value'));
    }

    public function testParseIntegerFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'integer_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertSame([123, 23, -23], $annotations->get('value'));
    }

    public function testParseFloatFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'float_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertSame([.45, 0.45, 45., -4.5], $annotations->get('value'));
    }

    public function testParseJsonFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'json_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertEquals(
            [
                ["x", "y"],
                json_decode('{"x": {"y": "z"}}'),
                json_decode('{"x": {"y": ["z", "p"]}}')
            ],
            $annotations->get('value')
        );
    }

    public function testParseEvalFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'eval_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertEquals(
            [
                86400000,
                [1, 2, 3],
                101000110111001100110100
            ],
            $annotations->get('value')
        );
    }

    public function testParseSingleValuesFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'single_values_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertEquals('foo', $annotations->get('param_a'));
        $this->assertEquals('bar', $annotations->get('param_b'));
    }

    public function testParseMultipleValuesFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'multiple_values_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertEquals(['x', 'y', 'z'], $annotations->get('value'));
    }

    public function testParseParseSameLineFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'same_line_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertSame(true, $annotations->get('get'));
        $this->assertSame(true, $annotations->get('post'));
        $this->assertSame(true, $annotations->get('ajax'));
        $this->assertSame(true, $annotations->get('alpha'));
        $this->assertSame(true, $annotations->get('beta'));
        $this->assertSame(true, $annotations->get('gamma'));
        $this->assertSame(null, $annotations->get('undefined'));
    }

    public function testNamespacedAnnotations()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'namespaced_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertSame('cheers!', $annotations->get('path.to.the.treasure'));
        $this->assertSame('the cake is a lie', $annotations->get('path.to.the.cake'));
        $this->assertSame('foo', $annotations->get('another.path.to.cake'));
    }

    public function testParseStrongTypedFixture()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'strong_typed_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $declarations = $annotations->get('value');
        $this->assertNotEmpty($declarations);
        $this->assertSame(
            [
            "abc", "45", // string
            45, -45, // integer
            .45, 0.45, 45.0, -4.5, 4., // float
            ],
            $declarations
        );

        $declarations = $annotations->get('json_value');
        $this->assertEquals(
            [
            ["x", "y"], // json
            json_decode('{"x": {"y": "z"}}'),
            json_decode('{"x": {"y": ["z", "p"]}}')
            ],
            $declarations
        );
    }

    public function testTolerateUnrecognizedTypes()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'non_recognized_type_fixture');
        $annotations = (new Parser($this->rules))->parse($reflection->getDocComment());

        $this->assertEquals("footype Tolerate me. DockBlocks can't be evaluated rigidly.", $annotations->get('value'));
    }

    /**
     * @expectedException Minime\Annotations\ParserException
     */
    public function testBadJSONValue()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'bad_json_fixture');
        (new Parser($this->rules))->parse($reflection->getDocComment());
    }

    /**
     * @expectedException Minime\Annotations\ParserException
     */
    public function testBadEvalValue()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'bad_eval_fixture');
        (new Parser($this->rules))->parse($reflection->getDocComment());
    }

    /**
     * @expectedException Minime\Annotations\ParserException
     */
    public function testBadIntegerValue()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'bad_integer_fixture');
        (new Parser($this->rules))->parse($reflection->getDocComment());
    }

    /**
     * @expectedException Minime\Annotations\ParserException
     */
    public function testBadFloatValue()
    {
        $reflection = new ReflectionProperty($this->Fixture, 'bad_float_fixture');
        (new Parser($this->rules))->parse($reflection->getDocComment());
    }
}
