<?php
/**
 * The TokenSequence class.
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

use TemplateEngine\Token;

/**
 * A TokenSequence contains many Token and offers some facilities to manipulate
 * them.
 *
 * @category Template
 * @package  TemplateEngine
 * @author   Frédéric BISSON <zigazou@free.fr>
 * @license  GNU GPL
 * @link     https://github.com/Zigazou/TemplateEngine
 */
class TokenSequence
{
    /**
     * An array containing every Token.
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * Builds a TokenSequence
     *
     * @param array $tokens An optional array of Tokens to initialize the
     *                      new TokenSequence
     */
    public function __construct(array $tokens = [])
    {
        $this->tokens = [];
        foreach ($tokens as $token) {
            $this->addToken($token);
        }
    }

    /**
     * Tests if the TokenSequence contains one or more Tokens.
     *
     * @return boolean true if the TokenSequence containes one or more Tokens,
     *                 false otherwise.
     */
    public function hasTokens()
    {
        return count($this->tokens) > 0;
    }

    /**
     * Returns the length of the TokenSequence (how many Tokens it contains).
     *
     * @return int The length of the TokenSequence.
     */
    public function length()
    {
        return count($this->tokens);
    }

    /**
     * Returns the Token located at a specified index.
     *
     * @param int $index The index of the wanted Token
     *
     * @return Token The Token.
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function at(int $index)
    {
        return $this->tokens[$index];
    }

    /**
     * Adds a new Token to a TokenSequence.
     *
     * @param Token $token The Token to add.
     *
     * @return TokenSequence Itself for chaining.
     */
    public function addToken(Token $token)
    {
        $this->tokens[] = $token;
        return $this;
    }

    /**
     * Tests if the TokenSequence starts with Tokens having specific types.
     *
     * @param array $types An array of Token types (string).
     * @param int   $index Optional starting index.
     *
     * @return boolean true if the TokenSequence starts with the specified
     *                 types, false otherwise.
     */
    public function startsWithTypes(array $types, int $index = 0)
    {
        if ($index < 0 or $index >= $this->length()) {
            return false;
        }

        // If the external type sequence's length is too big compared to our
        // token sequence, it cannot start it.
        if (count($types) > $this->length() - $index) {
            return false;
        }

        foreach ($types as $offset => $type) {
            // The tokens do not match, this sequence is not good.
            if ($this->tokens[$index + $offset]->type != $type) {
                return false;
            }
        }

        return true;
    }

    /**
     * Slice a TokenSequence into another TokenSequence.
     *
     * The resulting TokenSequence does not necessarily has the specified
     * length!
     *
     * @param int $start  The starting index
     * @param int $length How many Token to get.
     *
     * @return TokenSequence The sliced TokenSequence.
     */
    public function slice(int $start, int $length)
    {
        $tokens = new TokenSequence();

        // If the start is higher than the length, returns an empty sequence
        if ($start > $this->length()) {
            return $tokens;
        }

        // Calculates the end
        $end = $start + $length;
        if ($end > $this->length()) {
            $end = $this->length();
        }

        for ($index = $start; $index < $end; $index++) {
            $tokens->addToken($this->tokens[$index]);
        }

        return $tokens;
    }
}
