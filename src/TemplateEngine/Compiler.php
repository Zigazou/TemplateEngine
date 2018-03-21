<?php
namespace TemplateEngine;

use TemplateEngine\Action;
use TemplateEngine\TokenSequence;
use TemplateEngine\Program;

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

    public function unescapeString(string $string) {
        // Removes leading and ending double quotes
        $string = substr($string, 1, -1);

        $string = str_replace("\\\\", "\\", $string);
        $string = str_replace("\\\"", "\"", $string);

        return $string;
    }

    private function extractParameters(string $type, TokenSequence $tokens) {
        $parameters = array();
        foreach(self::PARAMETERS[$type] as $index) {
            $token = $tokens->at($index);

            if($token->type === "STRING") {
                // Unescape strings
                $content = $this->unescapeString($token->content);
            } elseif($token->type === "NUMBER") {
                $content = (float)$token->content;
            } else {
                $content = $token->content;
            }

            $parameters []= $content;
        }
        return $parameters;
    }

    private function sequenceMatch() {

    }

    private function createActions(TokenSequence $tokens) {
        $index = 0;
        $program = new Program();

        // Read all tokens
        while($index < $tokens->length()) {
            $validSequence = FALSE;

            // Try each sequence recognized by the language
            foreach(self::CHECKS as $action => $types) {
                if(!$tokens->startsWithTypes($types, $index)) continue;

                // The sequence matches the tokens
                $subTokens = $tokens->slice($index, count($types));
                $program->addAction(new Action(
                    $action,
                    $this->extractParameters($action, $subTokens),
                    $subTokens->at(0)->offset
                ));
                $validSequence = TRUE;
                $index += count($types);
                break;
            }

            // The sequence is not valid, throw an exception
            if(!$validSequence) {
                $offset = $tokens->at($index)->offset;
                throw new \ParseError("Invalid sequence at offset $offset");
            }
        }

        return $program;
    }

    private function buildTree(Program $actions, string $curType, int &$index) {
        $program = new Program();

        while($index < $actions->length()) {
            $action = $actions->at($index);
            $type = $action->type;
            $offset = $action->offset;

            if(in_array($type, array("direct", "variable"))) {
                $program->addAction($action);
            } elseif($type == "for") {
                $index++;

                $action->program = $this->buildTree(
                    $actions,
                    "for",
                    $index
                );

                $program->addAction($action);
            } elseif($type == "endfor") {
                if($curType != "for") {
                    throw new \ParseError("Unexpected $type at offset $offset");
                }
                return $program;
            } elseif(in_array($type, array("ifid", "ifstr", "ifnum"))) {
                $index++;
                $action->program = $this->buildTree(
                    $actions,
                    "if",
                    $index
                );

                if($actions->at($index)->type == "else") {
                    $index++;
                    $action->alternative = $this->buildTree(
                        $actions,
                        "if",
                        $index
                    );
                }

                $program->addAction($action);
            } elseif(in_array($type, array("else", "endif"))) {
                if($curType != "if") {
                    throw new \ParseError("Unexpected $type at offset $offset");
                }
                return $program;
            }

            $index++;
        }

        return $program;
    }

    private function createActionsTree(Program $actions) {
        $index = 0;

        return $this->buildTree($actions, "", $index);
    }

    public function compile(TokenSequence $tokens) {
        $program = $this->createActionsTree($this->createActions($tokens));

        return $program;
    }
}
