<?php
namespace TemplateEngine;

class Token {
    public $type = "";
    public $content = "";
    public $offset = 0;
    
    public function __construct(string $type, string $content, int $offset) {
        $this->type = $type;
        $this->content = $content;
        $this->offset = $offset;
    }
}
