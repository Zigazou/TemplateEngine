<?php
namespace TemplateEngine;

use \TemplateEngine\Token;
use \TemplateEngine\TokenSequence;

class Parser {
    /** The automaton
     * array(state => array(tokenType => array(regex, nextState, ignore)))
     */
    const AUTOMATON = array(
        "content" => array(
            "CMD_OPEN" => array("{{", "command", FALSE),
            "VAR_OPEN" => array("{%", "variable", FALSE),
            "DIRECT" => array("(\\\\{|\\\\\\\\|[^{])+", "content", FALSE),
        ),

        "command" => array(
            "CMD_CLOSE" => array("}}", "content", FALSE),
            "FOR" => array("for(?![[:alnum:]])", "command", FALSE),
            "ENDFOR" => array("endfor(?![[:alnum:]])", "command", FALSE),
            "IF" => array("if(?![[:alnum:]])", "command", FALSE),
            "ELSE" => array("else(?![[:alnum:]])", "command", FALSE),
            "IN" => array("in(?![[:alnum:]])", "command", FALSE),
            "ENDIF" => array("endif(?![[:alnum:]])", "command", FALSE),
            "ID" => array("[[:alpha:]][[:alnum:]]*", "command", FALSE),
            "STRING" => array('"(\\\\"|\\\\\\\\|[^"])*"', "command", FALSE),
            "NUMBER" => array("[0-9]+(.[0-9]+)?", "command", FALSE),
            "CMP" => array("(==|!=|<=|>=|<|>)", "command", FALSE),
            "BLANK" => array("[[:space:]]+", "command", TRUE),
        ),

        "variable" => array(
            "VAR_CLOSE" => array("%}", "content", FALSE),
            "ID" => array("[[:alpha:]][[:alnum:]]*", "variable", FALSE),
            "BLANK" => array("[[:space:]]+", "variable", TRUE),
        ),
    );

    public function __construct() {
    
    }

    /**
     * Parse a string and returns a TokenSequence. If an unexpected
     * character is found, it throws a ParseError exception.
     */
    public function parseString(string $string) {
        $state = "content";
        $offset = 0;
        $tokens = new TokenSequence();

        while($offset < strlen($string)) {
            $found = FALSE;

            // Try every possible transition from the current state
            foreach(self::AUTOMATON[$state] as $type => $transition) {
                list($regex, $nextState, $ignore) = $transition;

                /* Test if the regex matches */
                $found = preg_match(
                    '/' . $regex . '/As',
                    $string,
                    $matches,
                    0,
                    $offset
                );

                // A token has been recognized
                if($found) {
                    // Add it to the Token array if it should not be ignored
                    if(!$ignore) {
                        $tokens->addToken(
                            new Token($type, $matches[0], $offset)
                        );
                    }

                    // Update the offset
                    $offset += strlen($matches[0]);

                    // Go to the next state
                    $state = $nextState;
                    break;
                }
            }

            if(!$found) {
                // The automaton did not recognize a Token, throw an error
                throw new \ParseError(
                    "Unexpected character '" . $string[$offset] . "' " .
                    "at offset " . $offset
                );
            }
        }

        return $tokens;
    }
}
