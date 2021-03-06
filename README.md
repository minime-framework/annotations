Minime \ Annotations
==================

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
- ~~[DONE]~~ Dynamic annotations (eval type)
- ~~[DONE]~~ Namespaced annotations
- ~~[DONE]~~ Implicit boolean annotations
- ~~[DONE]~~ Multiple value annotations
- ~~[DONE]~~ Traits (for convenient integration)
- ~~[DONE]~~ API to filter and traverse annotations
- ~~[DONE]~~ Mutable AnnotationsBag (thanks to @nyamsprod)
- [TODO] Cache support [#7](https://github.com/marcioAlmada/annotations/issues/7)
- [TODO] Parser injection [#8](https://github.com/marcioAlmada/annotations/issues/8)

## Installation

Manually update `composer.json` with:
```json
{
  "require": {
    "minime/annotations": "~1.1"
  }
}
```

Or just use your terminal: `composer require minime/annotations:~1.1` :8ball:


## Basic Usage

### Using as a trait

The trait approach is useful for self / internal reflection:

```php
/**
 * @get @post @delete
 * @entity bar
 * @has-many Baz
 * @accept json ["json", "xml", "csv"]
 * @max integer 45
 * @delta float .45
 * @cache-duration eval 1000 * 24 * 60 * 60
 */
class FooController
{
    use Minime\Annotations\Traits\Reader;
}

$foo = new Foo();
$annotations = $foo->getClassAnnotations();

$annotations->get('get')      // > bool(true)
$annotations->get('post')     // > bool(true)
$annotations->get('delete')   // > bool(true)

$annotations->get('entity')   // > string(3) "bar"
$annotations->get('has-many') // > string(3) "Baz"

$annotations->get('accept')   // > array(3){ [0] => "json" [1] => "xml" [2] => "csv" }
$annotations->get('max')      // > int(45)
$annotations->get('delta')    // > double(0.45)
$annotations->get('cache-duration')    // > int(86400000)

$annotations->get('undefined')  // > null
```

Getting annotations from property and methods is easy too:

```php
$foo->getPropertyAnnotations('property_name');
$foo->getMethodAnnotations('method_name');
```

### Using the facade

The facade is useful when you want to inspect classes out of your logic domain:

```php
use Minime\Annotations\Facade;

Facade::getClassAnnotations('Full\Class\Name');
Facade::getPropertyAnnotations('Full\Class\Name', 'property_name');
Facade::getMethodAnnotations('Full\Class\Name', 'method_name');
```

### Grepping and traversing

Let's suppose you want to pick just a group of annotations:

```php
/**
 * @response.xml
 * @response.xls
 * @response.json
 * @response.csv
 * @method.get
 * @method.post
 */
class WebService
{
    use Minime\Annotations\Traits\Reader;
}

$annotations = (new WebService())->getClassAnnotations();
```

#### Grep all annotations within 'response' namespace

```php
$annotations->useNamespace('response')->export();
// > array(3){
// >    ["xml"]  => (bool) TRUE,
// >    ["xls"]  => (bool) TRUE,
// >    ["json"] => (bool) TRUE,
// >    ["csv"]  => (bool) TRUE
// > }
```

#### Chainning grep to get all annotations beginning with 'x' within 'response' namespace:

```php
$annotations->useNamespace('response')->grep('^x')->export();
// > array(3){
// >    ["xml"]  => (bool) TRUE,
// >    ["xls"]  => (bool) TRUE
// > }
```

#### Traversing results

```php
foreach($annotations->useNamespace('method') as $annotation => $value)
{
    // some behavior
}
```

## Coming Soon

* Annotations cache - any help?
* Possibility to inject a custom parser

If you know a great cache library, come aboard!

## Copyright

Copyright (c) 2013 Márcio Almada. Distributed under the terms of an MIT-style license. See LICENSE for details.


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/marcioAlmada/annotations/trend.png)](https://bitdeli.com/free "Bitdeli Badge")
