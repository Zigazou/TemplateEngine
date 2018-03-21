<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TemplateEngine\Parser;
use TemplateEngine\Token;
use TemplateEngine\TokenSequence;

final class ParserTest extends TestCase {
    public function testEmptyStringGivesNoToken() {
        $parser = new Parser();
        $this->assertEquals(0, $parser->parseString("")->length());
    }

    public function testParseDirect() {
        $string = "hello world!";
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = new TokenSequence(array(new Token("DIRECT", $string, 0)));
        $this->assertEquals($expected, $actual);
    }

    public function testParseVariable() {
        $string = "{% variable %}";
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = new TokenSequence(array(
            new Token("VAR_OPEN", "{%", 0),
            new Token("ID", "variable", 3),
            new Token("VAR_CLOSE", "%}", 12),
        ));
        $this->assertEquals($expected, $actual);
    }

    public function testParseDirectVariableDirect() {
        $string = "A{% variable.attribute %}B";
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = new TokenSequence(array(
            new Token("DIRECT", "A", 0),
            new Token("VAR_OPEN", "{%", 1),
            new Token("ID", "variable.attribute", 4),
            new Token("VAR_CLOSE", "%}", 23),
            new Token("DIRECT", "B", 25),
        ));
        $this->assertEquals($expected, $actual);
    }

    public function testParseCommand() {
        $string = '{{if a=="b\\"b"}}';
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = new TokenSequence(array(
            new Token("CMD_OPEN", "{{", 0),
            new Token("IF", "if", 2),
            new Token("ID", "a", 5),
            new Token("CMP", "==", 6),
            new Token("STRING", "\"b\\\"b\"", 8),
            new Token("CMD_CLOSE", "}}", 14),
        ));
        $this->assertEquals($expected, $actual);
    }

    public function testParseComparators() {
        $string = '{{==>=<=<>!=}}';
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = new TokenSequence(array(
            new Token("CMD_OPEN", "{{", 0),
            new Token("CMP", "==", 2),
            new Token("CMP", ">=", 4),
            new Token("CMP", "<=", 6),
            new Token("CMP", "<", 8),
            new Token("CMP", ">", 9),
            new Token("CMP", "!=", 10),
            new Token("CMD_CLOSE", "}}", 12),
        ));
        $this->assertEquals($expected, $actual);
    }

    public function testParseIdStringNumber() {
        $string = '{{hello225 225"abcd"278.3}}';
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = new TokenSequence(array(
            new Token("CMD_OPEN", "{{", 0),
            new Token("ID", "hello225", 2),
            new Token("NUMBER", "225", 11),
            new Token("STRING", "\"abcd\"", 14),
            new Token("NUMBER", "278.3", 20),
            new Token("CMD_CLOSE", "}}", 25),
        ));
        $this->assertEquals($expected, $actual);
    }

    public function testParseMultiline() {
        $string = "ABCD\n"
                . "{% variable %}\n"
                . "EFGH\n"
                . "{{ for item in items }}\n"
                . "  {% variable %}\n"
                . "{{ endfor }}\n"
                ;
        $parser = new Parser();
        $actual = $parser->parseString($string);

        $expected = new TokenSequence(array(
            new Token("DIRECT", "ABCD\n", 0),
            new Token("VAR_OPEN", "{%", 5),
            new Token("ID", "variable", 8),
            new Token("VAR_CLOSE", "%}", 17),
            new Token("DIRECT", "\nEFGH\n", 19),
            new Token("CMD_OPEN", "{{", 25),
            new Token("FOR", "for", 28),
            new Token("ID", "item", 32),
            new Token("IN", "in", 37),
            new Token("ID", "items", 40),
            new Token("CMD_CLOSE", "}}", 46),
            new Token("DIRECT", "\n  ", 48),
            new Token("VAR_OPEN", "{%", 51),
            new Token("ID", "variable", 54),
            new Token("VAR_CLOSE", "%}", 63),
            new Token("DIRECT", "\n", 65),
            new Token("CMD_OPEN", "{{", 66),
            new Token("ENDFOR", "endfor", 69),
            new Token("CMD_CLOSE", "}}", 76),
            new Token("DIRECT", "\n", 78)
        ));
        $this->assertEquals($expected, $actual);
    }
}

