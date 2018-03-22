<?php
/**
 * The Token class.
 *
 * @author Frédéric BISSON <zigazou@free.fr>
 */
namespace TemplateEngine;

/**
 * A Token is an element recognized by the Parser class.
 */
class Token {
    /**
     * @var string $type Token type
     */
    public $type = "";

    /**
     * @var string $content The content
     */
    public $content = "";

    /**
     * @var int $offset Offset in the template string where the Token was
     *                     found.
     */
    public $offset = 0;

    /**
     * Builds a Token.
     * 
     * @param string $type the Token type
     * @param string $content the Token content
     * @param int $offset the offset in the template string where the Token
     *                    begins.
     */
    public function __construct(string $type, string $content, int $offset) {
        $this->type = $type;
        $this->content = $content;
        $this->offset = $offset;
    }
}
