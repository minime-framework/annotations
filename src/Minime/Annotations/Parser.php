<?php

namespace Minime\Annotations;

use StrScan\StringScanner;

class Parser
{

    const TOKEN_ANNOTATION_NAME = '[a-zA-Z\_][a-zA-Z0-9\_\-\.]*';

    const TOKEN_ANNOTATION_IDENTIFIER = '@';

    /**
     * Parse a given docblock
     * 
     * @param string $raw_doc_block the doc block to parse
     * @return array Associative array with annotations and values
     */
    public function parse($raw_doc_block)
    {
        $parameters = [];
        $lines = array_map("rtrim", explode("\n",$raw_doc_block));
        foreach ($lines as $line) {
            $tokenizer = new StringScanner($line);
            $tokenizer->skip('/\s+\*\s+/');
            while (! $tokenizer->hasTerminated()) {
                $key = $tokenizer->scan('/' . self::TOKEN_ANNOTATION_IDENTIFIER . self::TOKEN_ANNOTATION_NAME . '/');
                if (! $key) { // next line when no annotation is found
                    $tokenizer->terminate();
                    continue;
                }

                $key = str_replace(self::TOKEN_ANNOTATION_IDENTIFIER, '', $key);
                $tokenizer->skip('/\s+/');
                if ('' == $tokenizer->peek() || $tokenizer->check('/\\'. self::TOKEN_ANNOTATION_IDENTIFIER .'/')) { // if implicit boolean
                    $parameters[$key] = true;
                    continue;
                }

                $type = 'dynamic';
                if ($tokenizer->check('/(string|integer|float|json)/')) { //if strong typed
                    $type = $tokenizer->scan('/\w+/');
                    $tokenizer->skip('/\s+/');
                }
                $value = $tokenizer->getRemainder();
                $parameters[$key][] = Parser::parseValue($value, $type);
            }
        }

        return $this->condense($parameters);
    }

    /**
     * Parse a given value against a specific type
     * @param string $value
     * @param string $type  the type to parse the value against
     *
     * @throws ParserException If the type is not recognized
     *
     * @return scalar|object
     */
    protected static function parseValue($value, $type = 'string')
    {
        $parse_method = 'parse'.ucfirst(strtolower($type));

        return Parser::{$parse_method}($value);
    }

    /**
     * Parse a given undefined type value
     * @param string $value
     *
     * @return scalar|object
     */
    protected static function parseDynamic($value)
    {
        $json = json_decode($value);
        if (JSON_ERROR_NONE == json_last_error()) {
            return $json;
        }

        return $value;
    }

    /**
     * Parse a given value
     * @param string $value
     *
     * @return scalar|object
     */
    protected static function parseString($value)
    {
        return $value;
    }

    /**
     * Filter a value to be an Integer
     * @param string $value
     *
     * @throws ParserException If $value is not an integer
     *
     * @return integer
     */
    protected static function parseInteger($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if (false === $value) {
            throw new ParserException("Raw value must be integer. Invalid value '{$value}' given.");
        }

        return $value;
    }

    /**
     * Filter a value to be a Float
     * @param string $value
     *
     * @throws ParserException If $value is not a float
     *
     * @return float
     */
    protected static function parseFloat($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);
        if (false === $value) {
            throw new ParserException("Raw value must be float. Invalid value '{$value}' given.");
        }

        return $value;
    }

    /**
     * Filter a value to be a Json
     * @param string $value
     *
     * @throws ParserException If $value is not a Json
     *
     * @return scalar|object
     */
    protected static function parseJson($value)
    {
        $json = json_decode($value);
        $error = json_last_error();
        if (JSON_ERROR_NONE != $error) {
            throw new ParserException("Invalid JSON string supplied.");
        }

        return $json;
    }

    private function condense(array $parameters)
    {
        return array_map(
            function ($value) {
                if (is_array($value) && 1 == count($value)) {
                    $value = $value[0];
                }

                return $value;
            },
            $parameters
        );
    }

}
