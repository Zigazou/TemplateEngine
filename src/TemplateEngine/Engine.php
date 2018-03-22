<?php
/**
 * The Engine class, the main class of TemplateEngine.
 *
 * @author Frédéric BISSON <zigazou@free.fr>
 */
namespace TemplateEngine;

use TemplateEngine\Parser;
use TemplateEngine\Compiler;

/**
 * An Engine takes a template string, an array of variables and generate a new
 * string by applying the variables to the template string.
 *
 * @example examples/template.html A simple template.
 * @example examples/forGnieark.php Code using the simple template.
 */
class Engine {
    /**
     * @var Program $program The compiled template.
     */
    public $program = NULL;

    /**
     * @var array Nested array of variables.
     */
    public $variables = array();

    /**
     * @var string $defaultFilter The default filter to use if no filter is
     *                            specified when displaying a variable.
     */
    public $defaultFilter = "raw";

    /**
     * Builds a template Engine.
     * 
     * The default filter is set to raw by default.
     */
    public function __construct() {
        $this->program = new Program();
        $this->variables = array();
        $this->defaultFilter = "raw";
    }

    /**
     * Set the default filter to use when rendering variables to the template.
     * 
     * Supported filters:
     * 
     * - **attr4**: escapes string for HTML 4.01 tag attributes
     * - **attr5**: escapes string for HTML 5 tag attributes
     * - **html4**: escapes string for HTML 4.01 text
     * - **html5**: escapes string for HTML 5 text
     * - **trim**: trim leading and ending whitespaces
     * - **raw**: do nothing
     * 
     * @param string $filter A string identifying the default filter. It can be
     *                       attr4, attr5, html4, html5, trim, raw
     * @return Engine Itself for chaining
     */
    public function setDefaultFilter(string $filter) {
        $this->defaultFilter = $filter;

        return $this;
    }
    
    /**
     * Load a template from a string and compiles it to a Program.
     * 
     * @example examples/template.html An example of template string.
     * 
     * @param string $string The template string
     * @return Engine Itself for chaining
     */
    public function loadTemplate(string $string) {
        $parser = new Parser();
        $tokens = $parser->parseString($string);

        $compiler = new Compiler();
        $this->program = $compiler->compile($tokens);

        return $this;
    }

    /**
     * Defines the variables to be used with the template.
     * 
     * Indexes of the array represents the variable names. A variable name
     * should only include alphanumerical characters.
     * 
     * @param array $variables The variables.
     * @return Engine Itself for chaining
     */
    public function setVariables(array $variables) {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Get the value of a variable given its identifier.
     * 
     * The identifier can include dot(s). It allows you to go deeper in a nested
     * array.
     * 
     * Example:
     * 
     *     $variables = array("a" => array("b" => 42));
     *     $this->getVariable("a.b"); // => returns 42
     *
     * @param string $identifier The identifier.
     * @return mixed The variable value or NULL if not found.
     */
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

    /**
     * Apply a filter to string.
     * 
     * @param string $filter The filter identifier.
     * @param string $string The string to be filtered.
     * @return mixed The filtered string.
     * @throws InvalidArgumentException when the filter is not known.
     */
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

    /**
     * Handler for direct Actions.
     * 
     * @param Action $action The Action to handle.
     * @param string $content The content to output.
     * @return string The output.
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function directRun(Action $action, string $content) {
        return $content;
    }

    /**
     * Handler for variable Actions.
     * 
     * @param Action $action The Action to handle.
     * @param string $variableName The variable identifier.
     * @return string The output.
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function variableRun(
        Action $action,
        string $variableName
    ) {
        $content = $this->getVariable($variableName);
        if($content === NULL) return "";

        return $this->applyFilter($this->defaultFilter, $content);
    }

    /**
     * Handler for varfilter Actions.
     * 
     * @param Action $action The Action to handle.
     * @param string $variableName The variable identifier.
     * @param string $filter The filter to apply to the variable value.
     * @return string The output.
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function varfilterRun(
        Action $action,
        string $variableName,
        string $filter
    ) {
        $content = $this->getVariable($variableName);
        if($content === NULL) return "";

        return $this->applyFilter($filter, $content);
    }

    /**
     * Handler for for Actions.
     * 
     * @param Action $action The Action to handle.
     * @param string $name The identifier which will be used as variable.
     * @param string $inName The name of the container to look data for.
     * @return string The output.
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function forRun(
        Action $action,
        string $name,
        string $inName
    ) {
        // Ignore if the container does not existe
        $container = $this->getVariable($inName);
        if($container === NULL) return "";

        // Throw an exception if the container is not an array
        if(!is_array($container)) {
            throw new \InvalidArgumentException("$inName is not an array");
        }

        $output = "";
        foreach($container as $item) {
            $this->variables[$name] = $item;
            $output .= $this->mainLoop($action->program);
        }
        
        return $output;
    }

    /**
     * Given two values and a comparator string, returns the result.
     * 
     * Comparator can be one of: ==, !=, >=, <=, >, <
     * 
     * @param mixed $first The first value to compare.
     * @param string $comparator The comparator to use.
     * @param mixed $second The second value to compare.
     * @return boolean TRUE if the comparison is TRUE, else FALSE. If the
     *                 comparator is not known, it also returns FALSE.
     */
    private function compare($first, string $comparator, $second) {
        switch($comparator) {
            case '==': return $first === $second;
            case '!=': return $first !== $second;
            case '>=': return $first >= $second;
            case '<=': return $first <= $second;
            case '>': return $first > $second;
            case '<': return $first < $second;
            default: return FALSE;
        }
    }

    /**
     * Handler for ifid Actions.
     * 
     * @param Action $action The Action to handle.
     * @param string $identifier1 the first variable identifier.
     * @param string $comparator the comparator.
     * @param string $identifier2 the second variable identifier.
     * @return string The output.
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function ifidRun(
        Action $action,
        string $identifier1,
        string $comparator,
        string $identifier2
    ) {
        // Ignore if an identifier does not existe
        $value1 = $this->getVariable($identifier1);
        if($value1 === NULL) return "";
        $value2 = $this->getVariable($identifier2);
        if($value2 === NULL) return "";

        // Do the comparison
        $comparisonIsTrue = $this->compare($value1, $comparator, $value2);

        return $comparisonIsTrue ? $this->mainLoop($action->program)
                                 : $this->mainLoop($action->alternative);
    }

    /**
     * Handler for ifstr Actions.
     * 
     * @param Action $action The Action to handle.
     * @param string $identifier the variable identifier.
     * @param string $comparator the comparator.
     * @param string $string the string to compare to.
     * @return string The output.
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function ifstrRun(
        Action $action,
        string $identifier,
        string $comparator,
        string $string
    ) {
        // Ignore if the identifier does not existe
        $value = $this->getVariable($identifier);
        if($value === NULL) return "";

        // Do the comparison
        $comparisonIsTrue = $this->compare($value, $comparator, $string);

        return $comparisonIsTrue ? $this->mainLoop($action->program)
                                 : $this->mainLoop($action->alternative);
    }

    /**
     * Handler for ifnum Actions.
     * 
     * @param Action $action The Action to handle.
     * @param string $identifier the variable identifier.
     * @param string $comparator the comparator.
     * @param float $number the number to compare to.
     * @return string The output.
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function ifnumRun(
        Action $action,
        string $identifier,
        string $comparator,
        float $number
    ) {
        // Ignore if the identifier does not existe
        $value = $this->getVariable($identifier);
        if($value === NULL) return "";

        // Do the comparison
        $comparisonIsTrue = $this->compare((float)$value, $comparator, $number);

        return $comparisonIsTrue ? $this->mainLoop($action->program)
                                 : $this->mainLoop($action->alternative);
    }

    /**
     * Main loop of the interpreter.
     * 
     * @param Program The program to execute.
     * @return string The output.
     */
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

    /**
     * Apply the variables to the compiled template and returns the result.
     * 
     * @return string The output.
     */
    public function output() {
        return $this->mainLoop($this->program);
    }
}
