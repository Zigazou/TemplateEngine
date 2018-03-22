<?php
/**
 * The Program class.
 *
 * @author Frédéric BISSON <zigazou@free.fr>
 */
namespace TemplateEngine;

/**
 * A Program is a collection of Actions.
 */
class Program {
    /**
     * @var array $actions List of Actions in the program
     */
    protected $actions = array();

    /**
     * Builds a Program.
     * 
     * @param array $actions Optional list of actions to add to our Program.
     */
    public function __construct(array $actions=array()) {
        $this->actions = array();
        foreach($actions as $action) $this->addAction($action);
    }

    /**
     * Tests if the Program contains one or more Actions.
     * 
     * @return boolean TRUE if the Program containes one or more Actions, FALSE
     *                 otherwise.
     */
    public function hasActions() {
        return count($this->actions) > 0;
    }

    /**
     * Returns the length of the Program (how many Actions it contains).
     * 
     * @return int The length of the Program.
     */
    public function length() {
        return count($this->actions);
    }

    /**
     * Returns the Action located at a specified index.
     * 
     * @param int $index The index of the wanted Action
     * @return Action The Action.
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function at(int $index) {
        return $this->actions[$index];
    }

    /**
     * Adds a new Action to a Program.
     * 
     * @param Action $action The Action to add.
     * @return Program Itself for chaining.
     */
    public function addAction(Action $action) {
        $this->actions []= $action;
    }

    /**
     * Tests if the Program starts with specific Actions types from another
     * Program.
     * 
     * @param Program $program An array of Action types.
     * @param int $index Optional starting index.
     * @return boolean TRUE if the Program starts with the specified
     *                 actions of another Program, FALSE otherwise.
     */
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
