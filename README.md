# README
Serializer of query filters from arrays or strings. Provides constraints and parsing logic for preconfigured filters

## Description
This is a PHP library that provides serialization/deserialization of abstract filters using query string, compatible with URL format. The main purpose of this library is to help developers to make more or less standard filters within URIs and convert it to neatly formed structure for further usage. Also it can provide (optionally) SQL builder that helps to append those filters to SQL queries. It is more less SQL implementation independent and can be used in various DBs: Doctrine, MySQL, SphinxQL etc. 

Filters can be easily configured using configs per each field


## Contents
1. [Installation](#installation)
1. [Features](#features)
1. [Usage](#usage)
    1. [String Format](#string-fmt)
        1. [Additional Examples](#string-fmt-additional-examples)
    1. [URL Query Format](#url-query-fmt)
        1. [Additional Examples](#url-query-fmt-additional-examples)
1. [Configuration](#config)
1. [Advanced Usage](#advanced-usage)
1. [TODOs](#todo)

## <a id="installation"></a>Installation
Add to composer.json
1. Add dependency to composer.json

2. Install new dependencies
   ```bash
   $ composer install

   ```

## <a id="features"></a>Features
- currently supports filters:
    - integer
    - datetime
    - date
    - enum
    - string
    - boolean
    - embedded
- 2 formats of string queries to parse supported:
    - string: `type:cool|foo:bar;baz|num:>10;<25|startDate:2000-10-01`
    - URL query: associative array of values or string - `_[type]=cool&_[foo][]=bar&_[foo][]=baz&_[user][id]=1&_[user][name]=Paul`
- customizable, configurable easily
- additional filters can be defined
- returned parsed filters can be multi-level arrays and collections of objects (defined by config)
- customizable filters and parser settings
- gives performance boost for developers to define complex logic of filtering records through collections without limiting them

## <a id="usage"></a>Usage
Currently only 2 query serializer formats supported: *String* and *URL Query*. 
You can implement your own using existing ones as examples. To see more usage examples, please refer to the provided [tests]

* *String* format is better fit for API requests, using single parameter to pass all filters with not too complex filtering implementation
* *URL Query* format can be fit for both regular web applications and API with more complex logic, embedded types 

### <a id="string-fmt"></a>String Format
This is a custom string format that implements simple and efficient way to set filters. It is short, human readable. 
See example below 
```php
// ...
use QueryFilterSerializer as App;

$options = new App\Config\Options();
$options->constraints = array('age' => array('type' => 'integer'));
$filterString = 'age:>=14;<18'; // string to parse

$serializer = new QuerySerializer(); // default encoder is StringEncoder so we can skip its init
$serializer->setOptions($options);
$filters = $serializer->unserialize($filterString);

var_export($filters);

// Output:
array (
  'age' => array (
    'constraints' => array (
      array (
        'condition' => 'gte',
        'value' => '14',
      ),
      array (
        'condition' => 'lt',
        'value' => '18',
      ),
    ),
    'type' => 'integer',
    'field' => 'age',
  ),
);

```

The main drawback may lie in its proper formatting on client side and escaping all special chars used for it, 
is more error prone than *URL Query* format when used without careful thinking beforehand. 
The good thing is that its that those are customizable and you can change them if needed or ensure that they won't appear in your
queries

#### <a id="string-fmt-additional-examples"></a>Additional examples

| Input                                                       | Config                                                                                                                                                   | Description                                                                                                                            |
|-------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| `name:John, hello`                                          | ['constraints' => ['name' => ['type' => 'string', 'name'=> 'title']]]                                                                                    | Will search for string "John, hello" within field "title" (see config)                                                                 |
| `name:John;hello;!Jake;Jim!`                                | ['constraints' => ['name' => ['type' => 'string', 'options' => ['use_not' => true, 'multiple' => true]]]]                                                | Will search for names, that conatin words "John" and "hello" and does not contain words "Jake" and "Jim"                               |
| `status:active`                                             | ['constraints' => ['status' => ['type' => 'string','options' => ['allowed' => ['active', 'passive']]]]]                                                  | Will search status that equals to "active". If given value will not have either "active" or "passive" words, then error will be thrown |
| `email:user@example.com;id:=2`                              | ['constraints' => ['email' => ['type' => 'string'], 'id' => ['type' => 'integer']]]                                                                      | Contains complex filters. Will search records that have email equal to "user@example.com" and id equal to "2"                          |
| `age:>=14,<20`                                              | ['constraints' => ['age' => ['type' => 'integer']],'build_sql' => true]                                                                                  | Integer range filter                                                                                                                   |
| `date_from:>=2015-11-28T16:59:13UTC,2015-9-28T16:59:13UTC`  | ['constraints' => ['date_from' => ['type' => 'datetime']]]                                                                                               | Will convert this set to =2015-08-28T16:59:13UTC. Datetime search                                                                      |
| `user:(name:John;status:active)`                            | ['constraints' => ['user' => ['type' => 'embedded', 'options' => ['constraints' => ['name' => ['type' => 'string'],'status' => ['type' => 'string']]]]]] | Embedded search. Will search for records that have reference to entity "user", which have name "John" and status "active"              |
| `age:<23,<2,<10`                                            | ['constraints' => ['age' => ['type' => 'integer']]]                                                                                                      | Will format it to age < 2                                                                                                              |
| `age:18,20,21`                                              | ['constraints' => ['age' => ['type' => 'integer']]]                                                                                                      | Will search for records that have age either 18, or 20 or 21. (SQL IN(...))                                                            |


### <a id="url-query-fmt"></a>URL Query Format
This option is easily implemented on client side and is based regular URL query string format. It may be parsed 
automatically by your PHP application or by serializer itself from string. Can be sent directly in HTML forms and 
may support the most complex cases for filtering data
```php
// ...
use QueryFilterSerializer as App;

$options = new App\Config\Options();
$options->constraints = array('age' => array('type' => 'integer'));
$filterQuery = '_[age][]=>=14&_[age][]=<18'; // string to parse
# $filterQuery = ["age" => [">=14", "<18"]];  // this is an alternative way to specify filters. Array used instead

$encoder = new App\Encoder\UrlQueryEncoder();
$options->filterTypeEncoders[App\Filter\Type\EmbeddedType::NAME] =
    App\Encoder\Filter\ArrayEmbeddedTypeEncoder::class; // required if embedded types should be supported
$serializer = new QuerySerializer($options, $encoder);
$filters = $serializer->unserialize($filterQuery);

var_export($filters);

// Output:
array (
  'age' => array (
    'constraints' => array (
      array (
        'condition' => 'gte',
        'value' => '14',
      ),
      array (
        'condition' => 'lt',
        'value' => '18',
      ),
    ),
    'type' => 'integer',
    'field' => 'age',
  ),
);

```
This is longer variant of query filtering, less human readable. Better for embedded and custom filter types to support
due to its solid URI component formatting rules supported by client & server from start.

If you need to use it as single GET param in URI it is possible to use formatting as following: 
`'http://example.com?filter=' + urlencode('_[foo]=bar&_[num]=>baz')`. 

#### <a id="url-query-fmt-additional-examples"></a>Additional examples

| Input                                                                            | Config                                                                                                                                                   | Description                                                                                                                            |
|----------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| `_[name]=John, hello`                                                            | ['constraints' => ['name' => ['type' => 'string', 'name'=> 'title']]]                                                                                    | Will search for string "John, hello" within field "title" (see config)                                                                 |
| `_[name][]=John&_[name][]=hello&_[name][]=!Jake&_[name][]=Jim!`                  | ['constraints' => ['name' => ['type' => 'string', 'options' => ['use_not' => true, 'multiple' => true]]]]                                                | Will search for names, that conatin words "John" and "hello" and does not contain words "Jake" and "Jim"                               |
| `_[status]=active`                                                               | ['constraints' => ['status' => ['type' => 'string','options' => ['allowed' => ['active', 'passive']]]]]                                                  | Will search status that equals to "active". If given value will not have either "active" or "passive" words, then error will be thrown |
| `_[email]=user@example.com&_[id]=2`                                              | ['constraints' => ['email' => ['type' => 'string'], 'id' => ['type' => 'integer']]]                                                                      | Contains complex filters. Will search records that have email equal to "user@example.com" and id equal to "2"                          |
| `_[age][]=>=14&_[age][]=<20`                                                     | ['constraints' => ['age' => ['type' => 'integer']],'build_sql' => true]                                                                                  | Integer range filter                                                                                                                   |
| `_[date_from][]=>=2015-11-28T16:59:13+03:00&_[date_from][]=2015-9-28T16:59:13UTC`| ['constraints' => ['date_from' => ['type' => 'datetime']]]                                                                                               | Will convert this set to =2015-08-28T16:59:13UTC. Datetime search                                                                      |
| `_[user][name]=John&_[user][status]=active`                                      | ['constraints' => ['user' => ['type' => 'embedded', 'options' => ['constraints' => ['name' => ['type' => 'string'],'status' => ['type' => 'string']]]]]] | Embedded search. Will search for records that have reference to entity "user", which have name "John" and status "active"              |
| `_[age][]=<23&_[age][]=<2&_[age][]=<10`                                          | ['constraints' => ['age' => ['type' => 'integer']]]                                                                                                      | Will format it to age < 2                                                                                                              |
| `_[age][]=18&_[age][]=20&_[age][]=21`                                            | ['constraints' => ['age' => ['type' => 'integer']]]                                                                                                      | Will search for records that have age either 18, or 20 or 21. (SQL IN(...))                                                            |

## <a id="config"></a>Configuration
TBD

## <a id="advanced-usage"></a>Advanced Usage
TBD


## <a id="todo"></a>TODOs

 - [ ] Write Tests
 - [ ] Support of recursive embedding filters
 - [ ] Add JS serializer
 - [x] Use more objects and less arrays. E.g. serializer options, return
 - [x] Use register method or DI for types add to serializer
 - [ ] Use interfaces in definitions instead of classes, when possible
 - [ ] Add loader interface and its implementation for filter types
 - [ ] Update docs
 - [ ] Implement Embedded type for UrlQueryEncoder
 - [ ] Use objects to populate resulting filter data
 - [ ] Implement serialization from array to string
 - [ ] Add *Configuration* section to README with info on how to configure serializer and its components
 - [ ] Add *Advanced Usage* section to README with info on how to customize serializer, extend filter types, 
 loaders, encoders etc.
 

License
----

MIT

[tests]:src/Tests/
