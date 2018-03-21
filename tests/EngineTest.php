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
}
