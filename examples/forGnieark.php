<?php
require_once("../vendor/autoload.php");

$template = file_get_contents("template.html");

$variables = array(
    "pageTitle" => "Poke @gnieark ;-p",
    "systems" => array(
        array(
            "url"     => "https://github.com/gnieark/tplBlock",
            "name"    => "tplBlock",
            "author"  => "Gnieark",
            "quality" => "simple and perfect",
        ),
        array(
            "url"     => "https://github.com/Zigazou/TemplateEngine",
            "name"    => "TemplateEngine",
            "author"  => "Zigazou",
            "quality" => "more complex than tplBlock",
        )
    ),
);

$engine = new \TemplateEngine\Engine();

$output = $engine->loadTemplate($template)
                 ->setVariables($variables)
                 ->setDefaultFilter("html5")
                 ->output();

print($output);

