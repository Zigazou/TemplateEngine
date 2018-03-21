<?php
namespace TemplateEngine;

use TemplateEngine\Parser;
use TemplateEngine\Compiler;

class Engine {
    public $program = NULL;
    public $variables = array();
    public $defaultFilter = "raw";

    public function __construct() {
        $this->program = new Program();
        $this->variables = array();
        $this->defaultFilter = "raw";
    }

    public function setDefaultFilter(string $filter) {
        $this->defaultFilter = $filter;

        return $this;
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

    private function getVariable(string $identifier) {
        $elements = explode(".", $identifier);

        $current = $this->variables;
        foreach($elements as $element) {
            if(!is_array($current)) return NULL;
            if(!isset($current[$element])) return NULL;
            $current = $current[$element];
        }

        return $current;
    }

    private function applyFilter(string $filter, string $string) {
        switch($filter) {
            case "attr4":
                return htmlspecialchars($string, ENT_QUOTES | ENT_HTML401);

            case "attr5":
                return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5);

            case "html4":
                return htmlspecialchars($string, ENT_NOQUOTES | ENT_HTML401);

            case "html5":
                return htmlspecialchars($string, ENT_NOQUOTES | ENT_HTML5);

            case "trim":
                return trim($string);

            case "raw":
                return $string;

            default:
                throw new \InvalidArgumentException("Unknown filter $filter");
        }
    }

    private function directRun(Action $action, string $content) {
        return $content;
    }

    private function variableRun(
        Action $action,
        string $variableName
    ) {
        $content = $this->getVariable($variableName);
        if($content === NULL) return "";

        return $this->applyFilter($this->defaultFilter, $content);
    }

    private function varfilterRun(
        Action $action,
        string $variableName,
        string $filter
    ) {
        $content = $this->getVariable($variableName);
        if($content === NULL) return "";

        return $this->applyFilter($filter, $content);
    }

    private function forRun(
        Action $action,
        string $name,
        string $inName
    ) {
        // Ignore if the container does not existe
        if(!isset($this->variables[$inName])) return "";

        // Throw an exception if the container is not an array
        if(!is_array($this->variables[$inName])) {
            throw new \InvalidArgumentException("$inName is not an array");
        }

        $output = "";
        foreach($this->variables[$inName] as $item) {
            $this->variables[$name] = $item;
            $output .= $this->mainLoop($action->program);
        }
        
        return $output;
    }

    private function compare($a, string $comparator, $b) {
        switch($comparator) {
            case '==': return $a === $b;
            case '!=': return $a !== $b;
            case '>=': return $a >= $b;
            case '<=': return $a <= $b;
            case '>': return $a > $b;
            case '<': return $a < $b;
            default: return FALSE;
        }
    }

    private function ifidRun(
        Action $action,
        string $identifier1,
        string $comparator,
        string $identifier2
    ) {
        // Ignore if an identifier does not existe
        if(!isset($this->variables[$identifier1])) return "";
        if(!isset($this->variables[$identifier2])) return "";

        // Do the comparison
        $comparisonIsTrue = $this->compare(
            $this->variables[$identifier1],
            $comparator,
            $this->variables[$identifier2]
        );

        if($comparisonIsTrue) {
            return $this->mainLoop($action->program);
        } else {
            return $this->mainLoop($action->alternative);
        }
    }

    private function ifstrRun(
        Action $action,
        string $identifier,
        string $comparator,
        string $string
    ) {
        // Ignore if the identifier does not existe
        if(!isset($this->variables[$identifier])) return "";

        // Do the comparison
        $comparisonIsTrue = $this->compare(
            $this->variables[$identifier],
            $comparator,
            $string
        );

        if($comparisonIsTrue) {
            return $this->mainLoop($action->program);
        } else {
            return $this->mainLoop($action->alternative);
        }
    }

    private function ifnumRun(
        Action $action,
        string $identifier,
        string $comparator,
        float $number
    ) {
        // Ignore if the identifier does not existe
        if(!isset($this->variables[$identifier])) return "";

        // Do the comparison
        $comparisonIsTrue = $this->compare(
            (float) $this->variables[$identifier],
            $comparator,
            $number
        );

        if($comparisonIsTrue) {
            return $this->mainLoop($action->program);
        } else {
            return $this->mainLoop($action->alternative);
        }
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
