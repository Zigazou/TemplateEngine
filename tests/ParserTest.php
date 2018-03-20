<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TemplateEngine\Parser;
use TemplateEngine\Token;

final class ParserTest extends TestCase {
    public function testEmptyStringGivesNoToken() {
        $parser = new Parser();
        $this->assertEmpty($parser->parseString(""));
    }

    public function testParseDirect() {
        $string = "hello world!";
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = array(new Token("DIRECT", $string, 0));
        $this->assertEquals($expected, $actual);
    }

    public function testParseVariable() {
        $string = "{% variable %}";
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = array(
            new Token("VAR_OPEN", "{%", 0),
            new Token("ID", "variable", 3),
            new Token("VAR_CLOSE", "%}", 12),
        );
        $this->assertEquals($expected, $actual);
    }

    public function testParseDirectVariableDirect() {
        $string = "A{% variable %}B";
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = array(
            new Token("DIRECT", "A", 0),
            new Token("VAR_OPEN", "{%", 1),
            new Token("ID", "variable", 4),
            new Token("VAR_CLOSE", "%}", 13),
            new Token("DIRECT", "B", 15),
        );
        $this->assertEquals($expected, $actual);
    }

    public function testParseCommand() {
        $string = '{{if a=="b\\"b"}}';
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = array(
            new Token("CMD_OPEN", "{{", 0),
            new Token("IF", "if", 2),
            new Token("ID", "a", 5),
            new Token("CMP_EQ", "==", 6),
            new Token("STRING", "\"b\\\"b\"", 8),
            new Token("CMD_CLOSE", "}}", 14),
        );
        $this->assertEquals($expected, $actual);
    }

    public function testParseComparators() {
        $string = '{{==>=<=<>!=}}';
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = array(
            new Token("CMD_OPEN", "{{", 0),
            new Token("CMP_EQ", "==", 2),
            new Token("CMP_GE", ">=", 4),
            new Token("CMP_LE", "<=", 6),
            new Token("CMP_LT", "<", 8),
            new Token("CMP_GT", ">", 9),
            new Token("CMP_NE", "!=", 10),
            new Token("CMD_CLOSE", "}}", 12),
        );
        $this->assertEquals($expected, $actual);
    }

    public function testParseIdStringNumber() {
        $string = '{{hello225 225"abcd"278.3}}';
        $parser = new Parser();
        $actual = $parser->parseString($string);
        $expected = array(
            new Token("CMD_OPEN", "{{", 0),
            new Token("ID", "hello225", 2),
            new Token("NUMBER", "225", 11),
            new Token("STRING", "\"abcd\"", 14),
            new Token("NUMBER", "278.3", 20),
            new Token("CMD_CLOSE", "}}", 25),
        );
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

        $expected = array(
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
        );
        $this->assertEquals($expected, $actual);
    }
}
