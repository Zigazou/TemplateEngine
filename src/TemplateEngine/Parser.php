<?php
/**
 * The Parser class.
 *
 * PHP version 7
 *
 * @category Template
 * @package  TemplateEngine
 * @author   Frédéric BISSON <zigazou@free.fr>
 * @license  GNU GPL
 * @link     https://github.com/Zigazou/TemplateEngine
 */
namespace TemplateEngine;

use \TemplateEngine\Token;
use \TemplateEngine\TokenSequence;

/**
 * The Parser class is juste a parser!
 *
 * It uses a simple 3-state automaton to do this.
 *
 * @category Template
 * @package  TemplateEngine
 * @author   Frédéric BISSON <zigazou@free.fr>
 * @license  GNU GPL
 * @link     https://github.com/Zigazou/TemplateEngine
 */
class Parser
{
    /**
     * The automaton.
     *
     * [ state => [ tokenType => [ regex, nextState, ignore ] ] ]
     *
     * @var array AUTOMATON
     */
    const AUTOMATON = [
        "content" => [
            "CMD_OPEN" => [ "{{", "command", false ],
            "VAR_OPEN" => [ "{%", "variable", false ],
            "DIRECT"   => [ "(\\\\{|\\\\\\\\|[^{])+", "content", false ],
        ],

        "command" => [
            "CMD_CLOSE" => [ "}}", "content", false ],
            "FOR"       => [ "for(?![[:alnum:]])", "command", false ],
            "ENDFOR"    => [ "endfor(?![[:alnum:]])", "command", false ],
            "IF"        => [ "if(?![[:alnum:]])", "command", false ],
            "ELSE"      => [ "else(?![[:alnum:]])", "command", false ],
            "IN"        => [ "in(?![[:alnum:]])", "command", false ],
            "ENDIF"     => [ "endif(?![[:alnum:]])", "command", false ],
            "ID"        => [ "[[:alpha:]][.[:alnum:]]*", "command", false ],
            "STRING"    => [ '"(\\\\"|\\\\\\\\|[^"])*"', "command", false ],
            "NUMBER"    => [ "[0-9]+(.[0-9]+)?", "command", false ],
            "CMP"       => [ "(==|!=|<=|>=|<|>)", "command", false ],
            "BLANK"     => [ "[[:space:]]+", "command", true ],
        ],

        "variable" => [
            "VAR_CLOSE" => [ "%}", "content", false ],
            "ID"        => [ "[[:alpha:]][.[:alnum:]]*", "variable", false ],
            "FILTER"    => [ ">[[:alpha:]][[:alnum:]]*", "variable", false ],
            "BLANK"     => [ "[[:space:]]+", "variable", true ],
        ],
    ];

    /**
     * Constructor (does nothing particular).
     */
    public function __construct()
    {
    }

    /**
     * Parse a string and returns a TokenSequence. If an unexpected
     * character is found, it throws a ParseError exception.
     *
     * @param string $string The string to parse.
     *
     * @return TokenSequence The string parsed in the form of a TokenSequence.
     *
     * @throws ParseError When an unexpected character is encountered.
     */
    public function parseString(string $string)
    {
        $state  = "content";
        $offset = 0;
        $tokens = new TokenSequence();

        while ($offset < strlen($string)) {
            $found = false;

            // Try every possible transition from the current state.
            foreach (self::AUTOMATON[$state] as $type => $transition) {
                list($regex, $nextState, $ignore) = $transition;

                // Test if the regex matches.
                $found = preg_match(
                    '/' . $regex . '/As',
                    $string,
                    $matches,
                    0,
                    $offset
                );

                // A token has been recognized.
                if ($found) {
                    // Add it to the Token array if it should not be ignored.
                    if (! $ignore) {
                        $tokens->addToken(
                            new Token($type, $matches[0], $offset)
                        );
                    }

                    // Update the offset.
                    $offset += strlen($matches[0]);

                    // Go to the next state.
                    $state = $nextState;
                    break;
                }
            }

            if (! $found) {
                // The automaton did not recognize a Token, throw an error
                throw new \ParseError(
                    "Unexpected character '$string[$offset]' at offset $offset"
                );
            }
        }

        return $tokens;
    }
}
