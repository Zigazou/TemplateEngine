<?php
/**
 * The Token class.
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

/**
 * A Token is an element recognized by the Parser class.
 *
 * @category Template
 * @package  TemplateEngine
 * @author   Frédéric BISSON <zigazou@free.fr>
 * @license  GNU GPL
 * @link     https://github.com/Zigazou/TemplateEngine
 */
class Token
{
    /**
     * Token type.
     *
     * @var string
     */
    public $type = "";

    /**
     * The content.
     *
     * @var string $content
     */
    public $content = "";

    /**
     * Offset in the template string where the Token was found.
     *
     * @var int $offset
     */
    public $offset = 0;

    /**
     * Builds a Token.
     *
     * @param string $type    the Token type.
     * @param string $content the Token content.
     * @param int    $offset  the offset in the template string where the Token
     *                        begins.
     */
    public function __construct(string $type, string $content, int $offset)
    {
        $this->type    = $type;
        $this->content = $content;
        $this->offset  = $offset;
    }
}
