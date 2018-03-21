<?php
namespace TemplateEngine;

use TemplateEngine\Parser;
use TemplateEngine\Compiler;

class Engine {
    public $program = NULL;
    public $variables = array();

    public function __construct() {
        $this->program = new Program();
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

    private function directRun(Action $action, string $content) {
        return $content;
    }

    private function variableRun(Action $action, string $variableName) {
        if(!isset($this->variables[$variableName])) return "";

        return $this->variables[$variableName];
    }

    private function forRun(Action $action, string $name, string $inName) {
        if(!isset($this->variables[$contName])) return "";

        $output = "";
        foreach($this->variables[$inName] as $item) {
            $this->variables[$name] = $item;
            $output .= $this->mainLoop($action->program);
        }
        
        return $output;
    }

    private function compare($a, string $cmp, $b) {
    
    }

    private function ifidRun(Action $action, string $id1, string $cmp, string $id2) {
        return "";
    }

    private function ifstrRun(Action $action, string $id, string $cmp, string $string) {
        return "";
    }

    private function ifnumRun(Action $action, string $id, string $cmp, string $number) {
        return "";
    }

    private function mainLoop(Program $program) {
        $output = "";

        for($index = 0; $index < $program->length(); $index++) {
            $action = $program->at($index);
            $output .= call_user_func_array(
                array($this, $action->type . "Run"),
                array_merge(array($action), $action->parameters)
            );
        }

        return $output;
    }

    public function output() {
        return $this->mainLoop($this->program);
    }
}
