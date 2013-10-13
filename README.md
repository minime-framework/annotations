Minime \ Annotations
====================

[![Build Status](https://travis-ci.org/marcioAlmada/annotations.png?branch=master)](https://travis-ci.org/marcioAlmada/annotations)
[![Coverage Status](https://coveralls.io/repos/marcioAlmada/annotations/badge.png?branch=master)](https://coveralls.io/r/marcioAlmada/annotations?branch=master)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/marcioAlmada/annotations/badges/quality-score.png?s=dba04c50549638ca00a6f22ff35903066f351909)](https://scrutinizer-ci.com/g/marcioAlmada/annotations/)
[![Latest Stable Version](https://poser.pugx.org/minime/annotations/v/stable.png)](https://packagist.org/packages/minime/annotations)
[![Total Downloads](https://poser.pugx.org/minime/annotations/downloads.png)](https://packagist.org/packages/minime/annotations)

Minime\Annotations is a very simple PHP library that lets you create APIs
that react to metadata with great flexibility and no headache.

## Features & Roadmap

- ~~[DONE]~~ Class, property and method annotations
- ~~[DONE]~~ <b>Optional</b> strong typed annotations: float, integer, string, json
- ~~[DONE]~~ Namespaced annotations
- ~~[DONE]~~ Implicit boolean annotations
- ~~[DONE]~~ Multiple value annotations
- ~~[DONE]~~ Traits (for convenient integration)
- ~~[DONE]~~ API to filter and traverse annotations
- [TODO] Cache support [#7](https://github.com/marcioAlmada/annotations/issues/7)
- [TODO] Parser injection [#8](https://github.com/marcioAlmada/annotations/issues/8)


## Installation

Manually update `composer.json` with:
```json
{
  "require": {
    "minime/annotations": "~2.0"
  }
}
```

Or use your terminal: `composer require minime/annotations:~2.0` :8ball:

## Basic Usage

```php
/**
 * FooController has some stuff annotated
 *
 * @get @post @delete
 * @cache disable
 * @response ["json", "xml", "csv"]
 */
class FooController
{
}
```

First we need to instantiate the annotations reader. The reader is responsible for retrieving annotations from a given class and pack then into a convenient collection:

```php

$reader = new \Minime\Annotations\Reader();
$annotations = $reader->getClassAnnotations('\FooController');
$annotations; // > object( Minime\Annotations\AnnotationsBag{} )
```

The `Minime\Anotations\AnnotationsBag` has the API to manipulate the retrieved annotations. Here is how to retrieve specific values:

```php
$annotations->get('get');        // > bool(true)
$annotations->get('post');       // > bool(true)
$annotations->get('delete');     // > bool(true)
$annotations->get('cache');      // > string(8) "disabled"
$annotations->get('response');   // > array(3){ [0] => "json" [1] => "xml" [2] => "csv" }
```

Undeclared annotations are considered null:

```php
$annotations->get('undefined');  // > null
```

Getting annotations from properties and methods is quite simple too:

```php
$annotations = $reader->getMethodAnnotations('\FooController', 'method');
$annotations = $reader->getPropertyAnnotations('\FooController', 'property');
```

## Traits

You can integrate <b>Minime\Annotations</b> to your project by simply using one of the available traits:

### Reader Trait

Use the <b>Minime\Annotations\Traits\Reader</b> to enable your class to get annotations from any reachable class:

```php
class MyReader
{
    use Minime\Annotations\Traits\Reader;

    public function behave()
    {
        $annotations = $this->getClassAnnotations('\My\Application\Kernel');
        if("enabled" === $annotations->get('application.logging'))
        {
            // activate logs
        }
    }
}
```

### Scoped Reader

Use the <b>Minime\Annotations\Traits\ScopedReader</b> to read annotations from current class context only (self reflection):

```php
/**
 * @model.automatic-timestamps
 * @model.entity bar
 * @model.has-many baz
 */
class FooModel
{
    use Minime\Annotations\Traits\ScopedReader;

    public function save()
    {
        $annotations = $this->getClassAnnotations();
        if($annotations->has('automatic-timestamps'))
        {
            // behavior to update timestamps
        }
    }
}
```


## Filtering and traversing annotations

Let's suppose you want to pick just a group of annotations:

```php
/**
 * @service.mime.xml
 * @service.mime.xls
 * @service.mime.json
 * @service.mime.csv
 *
 * @service.allow.get
 * @service.allow.post
 * @service.allow.delete
 *
 * @service.components.log   2
 * @service.components.cache 10
 */
class FancyWebService extends Service
{
    use Minime\Annotations\Traits\ScopedReader;
}

$annotations = (new FancyWebService())->getClassAnnotations();
```

Get all annotations from `service.mime` namespace:

```php
$annotations->useNamespace('service.mime')->export();
// > array(3){
// >    ["xml"]  => (bool) TRUE,
// >    ["xls"]  => (bool) TRUE,
// >    ["json"] => (bool) TRUE,
// >    ["csv"]  => (bool) TRUE
// > }
```

Within `service.mime` namespace, filter all mimes beginning with letter `x`:

```php
$annotations->useNamespace('service.mime')->grep('^x')->export();
// > array(3){
// >    ["xml"]  => (bool) TRUE,
// >    ["xls"]  => (bool) TRUE
// > }
```

Annotations bag can be used as an array too and can be traversed using a <b>foreach</b>:

```php
foreach($annotations->useNamespace('service.components') as $component => $level)
{
    $this->activateComponents($component, $level);
}
```

## Want to contribute?

Found a bug? Have an improvement idea? Want to contribute? Take a look at the [issues](https://github.com/marcioAlmada/annotations/issues), there is always something to be done. Please, send pull requests to desenv branch.

## Copyright

Copyright (c) 2013 Márcio Almada. Distributed under the terms of an MIT-style license. See LICENSE for details.

[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/marcioAlmada/annotations/trend.png)](https://bitdeli.com/free "Bitdeli Badge")