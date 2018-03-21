<?php
namespace TemplateEngine;

class Action {
    public $type = "";
    public $parameters = array();
    public $offset = 0;
    
    public function __construct(string $type, array $parameters, int $offset) {
        $this->type = $type;
        $this->parameters = $parameters;
        $this->offset = $offset;
    }
}
