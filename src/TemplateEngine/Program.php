<?php
namespace TemplateEngine;

class Program {
    protected $actions = array();
    
    public function __construct(array $actions=array()) {
        $this->actions = array();
        foreach($actions as $action) $this->addAction($action);
    }

    public function hasActions() {
        return count($this->actions) > 0;
    }

    public function length() {
        return count($this->actions);
    }

    public function at(int $index) {
        return $this->actions[$index];
    }

    public function addAction(Action $action) {
        $this->actions []= $action;
    }

    public function startsWith(Program $program, int $index=0) {
        // If the external program's length is too big compared to our program,
        // it cannot start it.
        if($program->length() > $this->length() - $index) {
            return FALSE;
        }

        foreach($program->actions as $offset => $tokenType) {
            // The actions do not match, this sequence is not good
            if($this->actions[$index + $offset]->type != $tokenType) {
                return FALSE;
            }
        }

        return TRUE;
    }
}
