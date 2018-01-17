# harmony-graphql-tool

This package try to make the using of [webonyx's Graphql implementation](https://github.com/webonyx/graphql-php) easyer when using [PSR 11's container](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md). To be easyer you must use [ServiceProvider](https://github.com/container-interop/service-provider)

It provides : 
- A Type Registry. This is intented to register Type and **not query**. What it does? It ask to the container if the Type asked by object is registered, else it use the Type name asked as a Classname (removing `Type` at the end of the Type asked if it exist). If the Type as a class using his name, it check if it implements `GraphqlTypeInterface`, if it does it use this type.
- `GraphqlQueryInterface`: All query's object should implements this interface to be able to be register into the schema
- `GraphqlTypeInterface`: All type's object should implements this interface to be discovered by the Type Registry
- `DefaultServiceProvider` : A service provider which create a graphql's server / a schema automatically

## How to use

### Using ServiceProvider

You must register the identifier's name list of instance of your query object (implementing `\PsCs\Harmony\Graphql\Tool\GraphqlQueryInterface`) under the name ` PsCs\Harmony\Graphql\Tool\GraphqlQueryInterface`.
By example:
- First define your TypeClass:
```php
namespace Foo;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MyType implements \PsCs\Harmony\Graphql\Tool\GraphqlTypeInterface {
    public $id = "foo";
    public function getId() {
        return $this->id;
    }
    static public function resolveField(MyType $value, $args, $context, ResolveInfo $info)  {
        switch ($info->fieldName) {
            case 'id':
              return $bill->getId();
            default:
              return null;
        }
    }
    static public function getType($typeRegistry): ObjectType {
        return new ObjectType([
            "name" => "MyType",
            "fields" => [
                "id" => Type::nonNull(Type::id())
            ],
            "resolveField" => [self::class, "resolveField"]
        ]);
    }
  }
```

- Secondly, create your Query object:

```php
namespace Foo;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\FieldDefinition;

class MyGraphqlQueryInterfaceImplementation extends MydataFinder implements \PsCs\Harmony\Graphql\Tool\GraphqlQueryInterface {

    public function getQueryField($typeRegistry): FieldDefinition {
        $that = $this;
        return FieldDefinition::create([
            "name" => "billPerYear",
            "type" => $typeRegistry->get((MyType::class)."Type"),
            'args'    => [
                'id' => Type::nonNull(Type::id())
            ],
            "resolve" => function($rootValue, $args) use ($that) {
                return $that->findOneById($args["id"]);
            }
        ]);
    }
  }
```

Alternatively, you can create an object which returns multiple query: 

```php
namespace Foo;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\FieldDefinition;

class MyGraphqlQueriesInterfaceImplementation extends MydataFinder implements \PsCs\Harmony\Graphql\Tool\GraphqlQueriesInterface {

    public function getQueryField($typeRegistry): array {
        $that = $this;
        return [FieldDefinition::create([
            "name" => "billPerYear",
            "type" => $typeRegistry->get((MyType::class)."Type"),
            'args'    => [
                'id' => Type::nonNull(Type::id())
            ],
            "resolve" => function($rootValue, $args) use ($that) {
                return $that->findOneById($args["id"]);
            }
        ])];
    }
  }
```

- Then create your serviceProvider

```php

namespace Foo\ServiceProvider;

use Interop\Container\ServiceProvider;
use GraphQL\Type\Schema;

use Foo\MyGraphqlQueryInterfaceImplementation;
use Interop\Container\ContainerInterface as Container;

class Graphql implements ServiceProvider {
 public function getServices()
    {
        return [
            (MyGraphqlQueryInterfaceImplementation::class)."Type" => [self::class, 'getMyGraphqlQueryInterfaceImplementation'],
            (\PsCs\Harmony\Graphql\Tool\GraphqlQueryInterface::class) => [self::class, 'getGraphqlQueryQueue'],
        ]; // By convention
    }
    public static function getMyGraphqlQueryInterfaceImplementation(Container $container) {
        return new MyGraphqlQueryInterfaceImplementation();
    }

    public static function getGraphqlQueryQueue(Container $container) {
        return [
            (MyGraphqlQueryInterfaceImplementation::class)."Type"
        ];
    }
}
```
Then register service provider needed:
```php 
    $container = require_once("container.php");
    $container->register(new \PsCs\Harmony\Graphql\Tool\ServiceProvider\DefaultServiceProvider());
    $container->register(new \Foo\ServiceProvider\Graphql);
    $standardServer = $container->get(\GraphQL\Server\StandardServer::class);
    // ....
```

## Without service provider

This package attempt to make easy the use of the library with service provider. If you do not use the service provider, this package cannot register automatically instances. You still can use the `Registry` class: 
```php

    $container = require_once("container.php");
    $registry = new \PsCs\Harmony\Graphql\Tool\Registry\Registry($container);
    $registry->get(MyTypeFoo::class);
    //...
```


## TODO

Implements type discovery using doctrine's annotation