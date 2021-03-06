<?php
/**
 * The Compiler class.
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

use TemplateEngine\Action;
use TemplateEngine\TokenSequence;
use TemplateEngine\Program;

/**
 * Compiler compiles a TokenSequence into a Program.
 *
 * It has two steps:
 *
 * - create actions from groups of tokens, ensuring the grammar is validated
 * - build tree of action blocks
 *
 * It is meant to be used by the Engine class.
 *
 * @category Template
 * @package  TemplateEngine
 * @author   Frédéric BISSON <zigazou@free.fr>
 * @license  GNU GPL
 * @link     https://github.com/Zigazou/TemplateEngine
 */
class Compiler
{
    /**
     * CHECKS describes authorized token sequences (the grammar). It is indexed
     * by the action type.
     */
    const CHECKS = [
        "direct"    => [ "DIRECT" ],
        "variable"  => [ "VAR_OPEN", "ID", "VAR_CLOSE" ],
        "varfilter" => [ "VAR_OPEN", "ID", "FILTER", "VAR_CLOSE" ],
        "for"       => [ "CMD_OPEN", "FOR", "ID", "IN", "ID", "CMD_CLOSE" ],
        "endfor"    => [ "CMD_OPEN", "ENDFOR", "CMD_CLOSE" ],
        "ifid"      => [ "CMD_OPEN", "IF", "ID", "CMP", "ID", "CMD_CLOSE" ],
        "ifstr"     => [ "CMD_OPEN", "IF", "ID", "CMP", "STRING", "CMD_CLOSE" ],
        "ifnum"     => [ "CMD_OPEN", "IF", "ID", "CMP", "NUMBER", "CMD_CLOSE" ],
        "else"      => [ "CMD_OPEN", "ELSE", "CMD_CLOSE" ],
        "endif"     => [ "CMD_OPEN", "ENDIF", "CMD_CLOSE" ],
    ];

    /**
     * PARAMETERS indicates which token content should be included when creating
     * the corresponding action (referenced by its index in the CHECKS element).
     * It is indexed by the action type.
     */
    const PARAMETERS = [
        "direct"    => [ 0 ],
        "variable"  => [ 1 ],
        "varfilter" => [ 1, 2 ],
        "for"       => [ 2, 4 ],
        "endfor"    => [],
        "ifid"      => [ 2, 3, 4 ],
        "ifstr"     => [ 2, 3, 4 ],
        "ifnum"     => [ 2, 3, 4 ],
        "else"      => [],
        "endif"     => [],
    ];

    /**
     * BUILDTREETTYPES is used by the buildTree method to determine which action
     * should be taken to handle different Action types.
     */
    const BUILDTREETYPES = [
        "direct"    => "direct",
        "variable"  => "direct",
        "varfilter" => "direct",
        "for"       => "for",
        "endfor"    => "endfor",
        "ifid"      => "if",
        "ifstr"     => "if",
        "ifnum"     => "if",
        "else"      => "endif",
        "endif"     => "endif",
    ];

    /**
     * The constructor (does nothing particular)
     */
    public function __construct()
    {
    }

    /**
     * Unescape a string.
     *
     * The string to unescape starts and ends with double quotes. The escape
     * character is \. There are only two characters that can be escaped: the
     * anti-slah \ and the double quote ".
     *
     * Example:
     *
     * - escaped string: "a\\\"b"
     * - unescaped string: a\"b
     *
     * @param string $escapedString the string to unescape.
     *
     * @return string the unescaped string.
     */
    public function unescapeString(string $escapedString)
    {
        // Removes leading and ending double quotes.
        $unescapedString = substr($escapedString, 1, -1);

        $unescapedString = str_replace("\\\\", "\\", $unescapedString);
        $unescapedString = str_replace("\\\"", "\"", $unescapedString);

        return $unescapedString;
    }

    /**
     * Extract and converts parameters for an Action from a TokenSequence.
     *
     * For example, when the TokenSequence contains the following Token types:
     * VAR_OPEN, ID, FILTER and VAR_CLOSE, this method will extract the second
     * and third Token content (ID and FILTER).
     *
     * @param string        $type   Action type, index of the PARAMETERS array.
     * @param TokenSequence $tokens tokens to extract parameters from.
     *
     * @return array the extracted and converted parameters.
     */
    private function extractParameters(string $type, TokenSequence $tokens)
    {
        $parameters = [];
        foreach (self::PARAMETERS[$type] as $index) {
            $token = $tokens->at($index);

            $content = $token->content;

            // Convert parameters according to the Token type.
            if ($token->type === "STRING") {
                // Unescape strings.
                $content = $this->unescapeString($content);
            } elseif ($token->type === "NUMBER") {
                // Force float type.
                $content = (float) $content;
            } elseif ($token->type === "FILTER") {
                // Remove the leading '>'.
                $content = substr($content, 1);
            }

            // Add the parameter.
            $parameters[] = $content;
        }
        return $parameters;
    }

    /**
     * Create a linear Program from a TokenSequence.
     *
     * A Program contains Actions. An Action is created from a TokenSequence
     * or part of a TokenSequence. Sequences are recognized based on the
     * PARAMETERS attribute class.
     *
     * @param TokenSequence $tokens Tokens to extract Actions from.
     *
     * @return Program the linear Program (no depth).
     *
     * @throws ParseError when an invalid sequence is encountered.
     */
    private function createActions(TokenSequence $tokens)
    {
        $index   = 0;
        $program = new Program();

        // Read all tokens.
        while ($index < $tokens->length()) {
            $validSequence = false;

            // Try each sequence recognized by the language.
            foreach (self::CHECKS as $action => $types) {
                if (! $tokens->startsWithTypes($types, $index)) {
                    continue;
                }

                // The sequence matches the tokens.
                $subTokens = $tokens->slice($index, count($types));
                $program->addAction(
                    new Action(
                        $action,
                        $this->extractParameters($action, $subTokens),
                        $subTokens->at(0)->offset
                    )
                );
                $validSequence = true;
                $index        += count($types);
                break;
            }

            // The sequence is not valid, throw an exception.
            if (! $validSequence) {
                $offset = $tokens->at($index)->offset;
                throw new \ParseError("Invalid sequence at offset $offset");
            }
        }

        return $program;
    }

    /**
     * Handler for direct, variable and varfilter Actions types.
     *
     * This method is called from the buildTree method.
     *
     * @param Program $actions Actions from which to build the tree.
     * @param int     $index   Current index in the Program.
     *
     * @return Action The action generated or null if the block ends here.
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function directHandler(
        Program $actions,
        int &$index
    ) {
        // Direct, variable and varfilter Actions can directly be added
        // to the Program.
        return $actions->at($index);
    }

    /**
     * Handler for for Actions types.
     *
     * This method is called from the buildTree method.
     *
     * @param Program $actions Actions from which to build the tree.
     * @param int     $index   Current index in the Program.
     *
     * @return Action The action generated or null if the block ends here.
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function forHandler(
        Program $actions,
        int &$index
    ) {
        $action = $actions->at($index);
        // For Action starts a nested block.
        $index++;
        $action->program = $this->buildTree($actions, "for", $index);
        return $action;
    }

    /**
     * Handler for endfor Actions types.
     *
     * This method is called from the buildTree method.
     *
     * @param Program $actions     Actions from which to build the tree.
     * @param int     $index       Current index in the Program.
     * @param string  $currentType The Action type of the current block.
     *
     * @return Action The action generated or null if the block ends here.
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function endforHandler(
        Program $actions,
        int &$index,
        string $currentType
    ) {
        if ($currentType === "for") {
            return null;
        }

        // Endfor Action must occur inside a for block.
        $action = $actions->at($index);
        $offset = $action->offset;
        $type   = $action->type;
        throw new \ParseError("Unexpected $type at offset $offset");
    }

    /**
     * Handler for ifid, ifstr or ifnum Actions types.
     *
     * This method is called from the buildTree method.
     *
     * @param Program $actions Actions from which to build the tree.
     * @param int     $index   Current index in the Program.
     *
     * @return Action The action generated or null if the block ends here.
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function ifHandler(
        Program $actions,
        int &$index
    ) {
        $action = $actions->at($index);
        // Ifid, ifstr or ifnum Action starts one or two nested blocks.
        $index++;
        $action->program = $this->buildTree($actions, "if", $index);

        // If the next Action is an else Action, create an alternative
        // block.
        if ($actions->at($index)->type == "else") {
            $index++;
            $action->alternative = $this->buildTree($actions, "if", $index);
        }

        return $action;
    }

    /**
     * Handler for else or endif Actions types.
     *
     * This method is called from the buildTree method.
     *
     * @param Program $actions     Actions from which to build the tree.
     * @param int     $index       Current index in the Program.
     * @param string  $currentType The Action type of the current block.
     *
     * @return Action The action generated or null if the block ends here.
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function endifHandler(
        Program $actions,
        int &$index,
        string $currentType
    ) {
        if ($currentType === "if") {
            return null;
        }

        // Else and endif Action must occur inside an ifid, ifstr or ifnum
        // block.
        $action = $actions->at($index);
        $offset = $action->offset;
        $type   = $action->type;
        throw new \ParseError("Unexpected $type at offset $offset");
    }

    /**
     * Build a tree Program from a linear Program.
     *
     * A Program contains Actions. An Action is created from a TokenSequence
     * or part of a TokenSequence. Sequences are recognized based on the
     * PARAMETERS attribute class.
     *
     * @param Program $actions Actions to read
     * @param string  $curType Current Action type (for, ifid, ifstr, ifnum)
     * @param int     $index   Current position in the Program
     *
     * @return Program the Program with all necessary children
     * @throws ParseError when an unexpected Action is encountered.
     */
    private function buildTree(Program $actions, string $curType, int &$index)
    {
        $program = new Program();

        while ($index < $actions->length()) {
            $type = $actions->at($index)->type;

            $action = call_user_func_array(
                [ $this, self::BUILDTREETYPES[$type] . "Handler" ],
                [ $actions, &$index, $curType ]
            );

            if ($action === null) {
                return $program;
            }

            $program->addAction($action);
            $index++;
        }

        return $program;
    }

    /**
     * CreateActionsTree is a front-end method for the buildTree method.
     *
     * @param Program $actions Linear Actions to read
     *
     * @return Program the Program with all necessary children
     */
    private function createActionsTree(Program $actions)
    {
        $index = 0;

        return $this->buildTree($actions, "", $index);
    }

    /**
     * Compile a TokenSequence to a Program.
     *
     * @param TokenSequence $tokens Tokens to compile
     *
     * @return Program the Program with all necessary children, ready to be
     *                 interpreted by the Engine class.
     */
    public function compile(TokenSequence $tokens)
    {
        $program = $this->createActionsTree($this->createActions($tokens));

        return $program;
    }
}
