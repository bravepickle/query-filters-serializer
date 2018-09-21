# README
This is a PHP library that provides serialization/deserialization of abstract filters using query string, compatible with URL format. The main purpose of this library is to help developers to make more or less standard filters within URIs and convert it to neatly formed structure for further usage. Also it can provide (optionally) SQL builder that helps to append those filters to SQL queries. It is more less SQL implementation independent and can be used in various DBs: Doctrine, MySQL, SphinxQL etc. 

Filters can be easily configured using configs per each field

## Contents
1. [Installation](#installation)
2. [Features](#features)
3. [Usage](#usage)
    1. [Additional Examples](#additional-examples)


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
    - string
    - boolean
    - embedded
- additional filters can be defined
- returned parsed filters can be mult-level arrays and collections of objects (defined by config)
- customizable filters and parser settings
- gives performance boost for developers to define complex logic of filtering records through collections without limiting them

## <a id="usage"></a>Usage
```
#!php
// define filter type to use for the specified fields and some additional options
$options = array('modifiers' => array('age' => array('type' => 'integer')), 'build_sql' => false);
$filterString = 'age:>=14,<18'; // string to parse

$serializer = new QuerySerializer();
$serializer->setOptions($options);
$filters = $serializer->unserialize($filterString);

print_r($filters);
/**
Will return:
array(
    'age' => array(
        'field' => 'age',
        'type' => 'integer',
        'modifiers' => array(
            array(
                'condition' => 'gte',
                'value' => 14,
            ),
            array(
                'condition' => 'lt',
                'value' => 18,
            ),            
        ),
    )
),
*/

```

### <a id="additional-examples"></a>Additional examples

| Input                                                     | Config                                                                                                                                                   | Description                                                                                                                            |
|-----------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------|
| name:John, hello                                          | ['constraints' => ['name' => ['type' => 'string', 'name'=> 'title']]]                                                                                    | Will search for string "John, hello" within field "title" (see config)                                                                 |
| name:John;hello;!Jake;Jim!                                | ['constraints' => ['name' => ['type' => 'string', 'options' => ['use_not' => true, 'multiple' => true]]]]                                                | Will search for names, that conatin words "John" and "hello" and does not contain words "Jake" and "Jim"                               |
| status:active                                             | ['constraints' => ['status' => ['type' => 'string','options' => ['allowed' => ['active', 'passive']]]]]                                                  | Will search status that equals to "active". If given value will not have either "active" or "passive" words, then error will be thrown |
| email:user@example.com&#124;id:=2                         | ['constraints' => ['email' => ['type' => 'string'], 'id' => ['type' => 'integer']]]                                                                      | Contains complex filters. Will search records that have email equal to "user@example.com" and id equal to "2"                          |
| age:>=14,<20                                              | ['constraints' => ['age' => ['type' => 'integer']],'build_sql' => true]                                                                                  | Integer range filter                                                                                                                   |
| 'date_from:>=2015-11-28T16:59:13UTC,2015-9-28T16:59:13UTC | ['constraints' => ['date_from' => ['type' => 'datetime']]]                                                                                               | Will convert this set to =2015-08-28T16:59:13UTC. Datetime search                                                                      |
| user:(name:John&#124;status:active)                       | ['constraints' => ['user' => ['type' => 'embedded', 'options' => ['constraints' => ['name' => ['type' => 'string'],'status' => ['type' => 'string']]]]]] | Embedded search. Will search for records that have reference to entity "user", which have name "John" and status "active"              |
| age:<23,<2,<10                                            | ['constraints' => ['age' => ['type' => 'integer']]]                                                                                                      | Will format it to age < 2                                                                                                              |
| age:18,20,21                                              | ['constraints' => ['age' => ['type' => 'integer']]]                                                                                                      | Will search for records that have age either 18, or 20 or 21. (SQL IN(...))                                                            |

To learn more on its usage, please refer to the provided [tests]


### TODOs

 - Write Tests
 - Support of recursive embedding filters
 - Add JS serializer
 - Use dependency injection by splitting: Encoder, Normalizer. With interfaces, similar to Symfony's implementation
 - Use more objects and less arrays. E.g. serializer options, return
 - Use register method or DI for types add to serializer
 - Use interfaces in definitions instead of classes, when possible
 - Add loader interface and its implementation for filter types
 - Update docs
 - Implement Embedded type for UrlQueryEncoder
 - Use objects to populate resulting filter data
 - Implement serialization from array to string

License
----

MIT

[tests]:QueryFilterSerializer/Tests/FiltersSerializerTest.php
