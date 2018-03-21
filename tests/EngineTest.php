<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TemplateEngine\Engine;

final class EngineTest extends TestCase {
    public function testHelloWorld() {
        $template = "Hello {% world %}\n";
        $variables = array("world" => "World!");

        $engine = new Engine();
        $actual = $engine->loadTemplate($template)
                         ->setVariables($variables)
                         ->output();

        $expected = "Hello World!\n";
        $this->assertEquals($expected, $actual);
    }

    public function testForLoop() {
        $template = "{{ for number in numbers }}{% number %}{{ endfor }}";
        $variables = array("numbers" => array(1, 2, 3, 4));

        $engine = new Engine();
        $actual = $engine->loadTemplate($template)
                         ->setVariables($variables)
                         ->output();

        $expected = "1234";
        $this->assertEquals($expected, $actual);
    }

    public function testNotAnArrayUsedInForLoop() {
        $this->expectException(InvalidArgumentException::class);

        $template = "{{ for a in b }}{% a %}{{ endfor }}";
        $variables = array("b" => "xyz");

        $engine = new Engine();
        $actual = $engine->loadTemplate($template)
                         ->setVariables($variables)
                         ->output();
    }

    public function testIfIdWithoutElse() {
        $template = "{{ if a == b }}a==b{{ endif }}";

        $engine = new Engine();
        $engine->loadTemplate($template);

        $result = $engine->setVariables(array("a" => "abc", "b" => "abc"))
                         ->output();

        $this->assertEquals("a==b", $result);

        $result = $engine->setVariables(array("a" => "abc", "b" => "xyz"))
                         ->output();

        $this->assertEquals("", $result);
    }

    public function testIfIdWithElse() {
        $template = "{{ if a == b }}a==b{{ else }}a!=b{{ endif }}";

        $engine = new Engine();
        $engine->loadTemplate($template);

        $result = $engine->setVariables(array("a" => "abc", "b" => "abc"))
                         ->output();

        $this->assertEquals("a==b", $result);

        $result = $engine->setVariables(array("a" => "abc", "b" => "xyz"))
                         ->output();

        $this->assertEquals("a!=b", $result);
    }

    public function testIfStrWithoutElse() {
        $template = "{{ if a == \"a\\\"\\\\a\" }}YES{{ endif }}";

        $engine = new Engine();
        $engine->loadTemplate($template);

        $result = $engine->setVariables(array("a" => "a\"\\a"))
                         ->output();

        $this->assertEquals("YES", $result);

        $result = $engine->setVariables(array("a" => "xyz"))
                         ->output();

        $this->assertEquals("", $result);
    }

    public function testIfStrWithElse() {
        $template = "{{ if a == \"a\\\"\\\\a\" }}YES{{ else }}NO{{ endif }}";

        $engine = new Engine();
        $engine->loadTemplate($template);

        $result = $engine->setVariables(array("a" => "a\"\\a"))
                         ->output();

        $this->assertEquals("YES", $result);

        $result = $engine->setVariables(array("a" => "xyz"))
                         ->output();

        $this->assertEquals("NO", $result);
    }

    public function testIfNumWithoutElse() {
        $template = "{{ if a == 42 }}YES{{ endif }}";

        $engine = new Engine();
        $engine->loadTemplate($template);

        $result = $engine->setVariables(array("a" => 42))
                         ->output();

        $this->assertEquals("YES", $result);

        $result = $engine->setVariables(array("a" => 33))
                         ->output();

        $this->assertEquals("", $result);
    }

    public function testIfNumWithElse() {
        $template = "{{ if a == 42 }}YES{{ else }}NO{{ endif }}";

        $engine = new Engine();
        $engine->loadTemplate($template);

        $result = $engine->setVariables(array("a" => 42))
                         ->output();

        $this->assertEquals("YES", $result);

        $result = $engine->setVariables(array("a" => 33))
                         ->output();

        $this->assertEquals("NO", $result);
    }

    public function testVariableNameComponents() {
        $template = "{% a.b.c.d %}";
 
        $engine = new Engine();
        $engine->loadTemplate($template);

        $variables = array("a" => array("b" => array("c" => array("d" => 42))));

        $result = $engine->setVariables($variables)
                         ->output();

        $this->assertEquals("42", $result);

        $variables = array("a" => array("b" => array("x" => array("d" => 42))));

        $result = $engine->setVariables($variables)
                         ->output();

        $this->assertEquals("", $result);
    }

    public function testVariableFilter() {
        $template = "{% a>trim %}";
 
        $engine = new Engine();
        $engine->loadTemplate($template);

        $result = $engine->setVariables(array("a" => "   x   "))
                         ->output();

        $this->assertEquals("x", $result);
    }
}
