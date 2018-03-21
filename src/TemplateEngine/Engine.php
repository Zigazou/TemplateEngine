<?php
namespace TemplateEngine;

use TemplateEngine\Parser;
use TemplateEngine\Compiler;

class Engine {
    public $program = array();
    public $variables = array();

    private $pc = 0;
    private $stack = array();

    public function __construct() {
        $this->program = array();
        $this->variables = array();
    }
    
    public function loadTemplate(string $string) {
        $parser = new Parser();
        $tokens = $parser->parseString($string);

        $compiler = new Compiler();
        $this->program = $compiler->compile($tokens);

        return $this;
    }

    public function setVariables(array $variables) {
        $this->variables = $variables;

        return $this;
    }

    private function directRun(string $content) {
        $this->pc++;
        return $content;
    }

    private function variableRun(string $variableName) {
        $this->pc++;
        if(!isset($this->variables[$variableName])) return "";

        return $this->variables[$variableName];
    }

    private function forRun(string $item, string $container) {
        $this->pc++;
        return "";
    }

    private function endforRun() {
        $this->pc++;
        return "";
    }

    private function compare($a, string $cmp, $b) {
    
    }

    private function ifidRun(string $id1, string $cmp, string $id2) {
        $this->pc++;
        return "";
    }

    private function ifstrRun(string $id, string $cmp, string $string) {
        $this->pc++;
        return "";
    }

    private function ifnumRun(string $id, string $cmp, string $number) {
        $this->pc++;
        return "";
    }

    private function elseRun() {
        $this->pc++;
        return "";
    }

    private function endifRun() {
        $this->pc++;
        return "";
    }

    public function output() {
        // Initializes the processor
        $this->pc = 0;
        $this->stack = array();

        $output = "";

        while($this->pc < $this->program->length()) {
            $action = $this->program->at($this->pc);
            $output .= call_user_func_array(
                array($this, $action->type . "Run"),
                $action->parameters
            );
        }

        return $output;
    }
}
