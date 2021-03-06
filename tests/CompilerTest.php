<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TemplateEngine\Parser;
use TemplateEngine\Compiler;
use TemplateEngine\Program;
use TemplateEngine\Token;
use TemplateEngine\Action;

final class CompilerTest extends TestCase {
    public function testCheckValid() {
        $string = "ABCD\n"
                . "{% variable %}\n"
                . "EFGH\n"
                . "{{ for item in items }}\n"
                . "  {% variable %}\n"
                . "{{ endfor }}\n"
                ;

        $parser = new Parser();
        $tokens = $parser->parseString($string);

        $compiler = new Compiler();
        $program = $compiler->compile($tokens);

        $insideLoop = new Program(array(
            new Action("direct", array("\n  "), 48),
            new Action("variable", array("variable"), 51),
            new Action("direct", array("\n"), 65),
        ));

        $forLoop = new Action("for", array("item", "items"), 25);
        $forLoop->program = $insideLoop;

        $expected = new Program(array(
            new Action("direct", array("ABCD\n"), 0),
            new Action("variable", array("variable"), 5),
            new Action("direct", array("\nEFGH\n"), 19),
            $forLoop,
            new Action("direct", array("\n"), 78),
        ));

        $this->assertEquals($expected, $program);
    }

    public function testInvalidBlock() {
        $this->expectException(ParseError::class);

        $string = "{{ for item in items }}\n"
                . "  {% variable %}\n"
                . "{{ endif }}\n"
                ;

        $parser = new Parser();
        $tokens = $parser->parseString($string);

        $compiler = new Compiler();
        $program = $compiler->compile($tokens);
    }

    public function testCheckInvalidFor() {
        $this->expectException(ParseError::class);

        $string = "{{ for for }}";
        $parser = new Parser();
        $tokens = $parser->parseString($string);

        $compiler = new Compiler();
        $compiler->compile($tokens);
    }

    public function testCheckUnclosedVariable() {
        $this->expectException(ParseError::class);

        $string = "{% variable";
        $parser = new Parser();
        $tokens = $parser->parseString($string);

        $compiler = new Compiler();
        $compiler->compile($tokens);
    }
}
