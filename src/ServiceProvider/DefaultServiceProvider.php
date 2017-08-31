<?php

namespace PsCs\Harmony\Graphql\Tool\ServiceProvider;

use Interop\Container\ServiceProvider;
use GraphQL\Type\Schema;

use GraphQL\Type\Definition\ObjectType;
use Interop\Container\ContainerInterface as Container;
use GraphQL\Server\StandardServer;
use PsCs\Harmony\Graphql\Tool\GraphqlQueryInterface;
use PsCs\Harmony\Graphql\Tool\GraphqlTypeInterface;
use PsCs\Harmony\Graphql\Tool\Registry\Registry;

class DefaultServiceProvider implements ServiceProvider {
 public function getServices()
    {
        return [
            (GraphqlQueryInterface::class) => [self::class, 'getGraphqlQueryQueue'],
            (Schema::class) => [self::class, 'getSchema'],
            (StandardServer::class) => [self::class, 'getGraphqlHandler'],
            Registry::class => [self::class, "getRegistry"]
        ]; // By convention
    }

    public static function getRegistry(Container $container): Registry {
        return new Registry($container);
    }

    public static function getGraphqlQueryQueue(Container $container, callable $previous = null)  {
        if ($previous) {
            return $previous();
        }
        return [];
    }

    public static function getSchema(Container $container): Schema {
        $fields = [];
        $graphqlQueryQueue = $container->get(GraphqlQueryInterface::class);
        $registry = $container->get(Registry::class);
        foreach ($graphqlQueryQueue as $query) {
            $q = $container->get($query);
            if ($q instanceof GraphqlQueryInterface) {
                $fields[] = $q->getQueryField($registry);
            }
            elseif ($q instanceof \GraphQL\Type\Definition\FieldDefinition) {
                $fields[] = $q;
            }
        }
        return new Schema([
            "query" => new ObjectType([
                'name'   => 'Query',
                'fields' => $fields
            ])
        ]);
    }

    public static function getGraphqlHandler(Container $container): StandardServer {
        return new StandardServer(["schema" => $container->get(Schema::class)]);
    }

}