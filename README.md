TemplateEngine
==============

[![Build Status](https://travis-ci.org/Zigazou/TemplateEngine.svg?branch=master)](https://travis-ci.org/Zigazou/TemplateEngine)

TemplateEngine is a simple template engine for PHP.

Do not consider it seriously :-)

Sample
------

```html
<html>
  <head>
    <meta charset="utf-8" />
    <title>{% pageTitle %}</title>
  </head>
  <body>
    <h1>{% pageTitle %}</h1>
    <ul>
    {{ for system in systems }}
      <li>
        <a href="{% system.url>attr5 %}">{% system.name %}</a>
        by {% system.author %} is {% system.quality %}
        {{ if system.name == "tplBlock" }}(maybe){{ else }}(really?){{ endif }}
      </li> 
    {{ endfor }}
    </ul>
  </body>
</html>
```

Parsed with this code:

```php
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
```

will return:

```html
<html>
  <head>
    <meta charset="utf-8" />
    <title>Poke @gnieark ;-p</title>
  </head>
  <body>
    <h1>Poke @gnieark ;-p</h1>
    <ul>
    
      <li>
        <a href="https://github.com/gnieark/tplBlock">tplBlock</a>
        by Gnieark is simple and perfect
        (maybe)
      </li> 
    
      <li>
        <a href="https://github.com/Zigazou/TemplateEngine">TemplateEngine</a>
        by Zigazou is more complex than tplBlock
        (really?)
      </li> 
    
    </ul>
  </body>
</html>
```

The sources are available in the `examples` subdirectory.

Conception choices
------------------

When Gnieark decided to write a template system, I decided to write mine :o)

I chose to write it in the form of a parser/compiler/interpreter because it
can be easily improved to include more features.

