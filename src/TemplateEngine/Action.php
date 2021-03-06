<?php
/**
 * The Action class.
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

use TemplateEngine\Program;

/**
 * An Action is a step or instruction in a Program.
 *
 * @category Template
 * @package  TemplateEngine
 * @author   Frédéric BISSON <zigazou@free.fr>
 * @license  GNU GPL
 * @link     https://github.com/Zigazou/TemplateEngine
 */
class Action
{
    /**
     * Action type.
     *
     * @var string $type
     */
    public $type = "";

    /**
     * List of parameters value.
     *
     * @var array $parameters
     */
    public $parameters = [];

    /**
     * Offset where this Action starts in a template string.
     *
     * @var int $offset
     */
    public $offset = 0;

    /**
     * If the Action is a block, this attribute contains the block Actions.
     *
     * @var Program $program
     */
    public $program = null;

    /**
     * If the Action is an if block, this attribute contains the block Actions
     * to execute in the else part of the if Action.
     *
     * @var Program $alternative
     */
    public $alternative = null;

    /**
     * Constructs a Program object
     *
     * If the Action represents a block, its Program must be added after the
     * object creation.
     *
     * @param string $type       A string representing the Action type
     * @param array  $parameters The list of parameters
     * @param int    $offset     The offset at which the Action starts in the
     *                           template string.
     */
    public function __construct(string $type, array $parameters, int $offset)
    {
        $this->type        = $type;
        $this->parameters  = $parameters;
        $this->offset      = $offset;
        $this->program     = new Program();
        $this->alternative = new Program();
    }

    /**
     * Tells if the Action contains children Actions (in the case of a block).
     *
     * @return boolean true if the Action contains children Actions, false
     *                 otherwise
     */
    public function hasActions()
    {
        return $this->program->hasActions()
            or $this->alternative->hasActions();
    }

    /**
     * Add an Action as a child.
     *
     * @param Action $action The Action to add as a child to our Action.
     *
     * @return Action Itself for chaining.
     */
    public function addAction(Action $action)
    {
        $this->program->addAction($action);
        return $this;
    }

    /**
     * Add an Action as an alternative child.
     *
     * @param Action $action The Action to add as an alternative child to our
     *                       Action.
     *
     * @return Action Itself for chaining.
     */
    public function addAlternative(Action $action)
    {
        $this->alternative->addAction($action);
        return $this;
    }
}
