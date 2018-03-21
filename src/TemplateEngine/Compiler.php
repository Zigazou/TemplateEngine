<?php
namespace TemplateEngine;
use TemplateEngine\Action;

class Compiler {
    const CHECKS = array(
        "direct" => array("DIRECT"),
        "variable" => array("VAR_OPEN", "ID", "VAR_CLOSE"),
        "for" => array("CMD_OPEN", "FOR", "ID", "IN", "ID", "CMD_CLOSE"),
        "endfor" => array("CMD_OPEN", "ENDFOR", "CMD_CLOSE"),
        "ifid" => array("CMD_OPEN", "IF", "ID", "CMP", "ID", "CMD_CLOSE"),
        "ifstr" => array("CMD_OPEN", "IF", "ID", "CMP", "STRING", "CMD_CLOSE"),
        "ifnum" => array("CMD_OPEN", "IF", "ID", "CMP", "NUMBER", "CMD_CLOSE"),
        "else" => array("CMD_OPEN", "ELSE", "CMD_CLOSE"),
        "endif" => array("CMD_OPEN", "ENDIF", "CMD_CLOSE"),
    );

    const PARAMETERS = array(
        "direct" => array(0),
        "variable" => array(1),
        "for" => array(2, 4),
        "endfor" => array(),
        "ifid" => array(2, 3, 4),
        "ifstr" => array(2, 3, 4),
        "ifnum" => array(2, 3, 4),
        "else" => array(),
        "endif" => array(),
    );

    public function __construct() {

    }

    public function extractParameters(string $actionType, array $tokens) {
        $parameters = array();
        foreach(self::PARAMETERS[$actionType] as $index) {
            $parameters []= $tokens[$index]->content;
        }
        return $parameters;
    }

    public function compile(array $tokens) {
        $index = 0;
        $program = array();

        // Read all tokens
        while($index < count($tokens)) {
            $validSequence = FALSE;

            // Try each sequence recognized by the language
            foreach(self::CHECKS as $action => $sequence) {
                $sequenceMatch = TRUE;

                // Try to read the sequence from the Token list
                foreach($sequence as $offset => $tokenType) {
                    // Do not try to read more token than available
                    if($index + $offset >= count($tokens)) {
                        $sequenceMatch = FALSE;
                        break;
                    }

                    // The tokens do not match, this sequence is not good
                    if($tokens[$index + $offset]->type != $tokenType) {
                        $sequenceMatch = FALSE;
                        break;
                    }
                }

                // The sequence matches the tokens
                if($sequenceMatch) {
                    $subTokens = array_slice($tokens, $index, count($sequence));
                    $program []= new Action(
                        $action,
                        $this->extractParameters($action, $subTokens),
                        $subTokens[0]->offset
                    );
                    $validSequence = TRUE;
                    $index += count($sequence);
                    break;
                }
            }

            // The sequence is not valid, throw an exception
            if(!$validSequence) {
                throw new \ParseError(
                    "Invalid sequence at offset " . $tokens[$index]->offset
                );
            }
        }

        return $program;
    }
}
