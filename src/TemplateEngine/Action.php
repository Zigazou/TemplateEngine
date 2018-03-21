<?php
namespace TemplateEngine;

use TemplateEngine\Program;

class Action {
    public $type = "";
    public $parameters = array();
    public $offset = 0;

    public $program = NULL;
    public $alternative = NULL;
    
    public function __construct(string $type, array $parameters, int $offset) {
        $this->type = $type;
        $this->parameters = $parameters;
        $this->offset = $offset;
        $this->program = new Program();
        $this->alternative = new Program();
    }

    public function hasActions() {
        return $this->program->hasActions()
            or $this->alternative->hasActions();
    }

    public function addAction(Action $action) {
        $this->program->addAction($action);
    }

    public function addAlternative(Action $action) {
        $this->alternative->addAction($action);
    }
}
