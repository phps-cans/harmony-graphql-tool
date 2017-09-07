<?php

namespace PsCs\Harmony\Graphql\Tool\ServiceProvider;

use Interop\Container\ServiceProvider;
use GraphQL\Type\Schema;

use Interop\Container\ContainerInterface as Container;
use GraphQL\Server\StandardServer;
use PsCs\Harmony\Graphql\Tool\GraphqlQueryInterface;
use PsCs\Harmony\Graphql\Tool\GraphqlQueriesInterface;
use PsCs\Harmony\Graphql\Tool\GraphqlTypeInterface;
use PsCs\Harmony\Graphql\Tool\Registry\Registry;
use PsCs\Harmony\Graphql\Tool\Factory\WebonyxSchemaFactory;

class DefaultServiceProvider implements ServiceProvider {
 public function getServices()
    {
        return [
            (GraphqlQueryInterface::class) => [self::class, 'getGraphqlQueryQueue'],
            (GraphqlQueriesInterface::class) => [self::class, 'getGraphqlQueriesQueue'],
            (Schema::class) => [self::class, 'getSchema'],
            (StandardServer::class) => [self::class, 'getGraphqlHandler'],
            Registry::class => [self::class, "getRegistry"],
            WebonyxSchemaFactory::class => [self::class, "getFactory"]
        ]; // By convention
    }

    public static function getGraphqlQueriesQueue(Container $container, callable $previous = null) {
        if ($previous) {
            return $previous();
        }
        return [];
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

    private static function transformToObjectQueue($graphqlQueryQueue, Container $container) {
        $queue = [];
        foreach ($graphqlQueryQueue as $q) {
            $queue[] = $container->get($q);
        }
        return $queue;
    }

    public static function getSchema(Container $container): Schema {
        $schemaFactory = $container->get(WebonyxSchemaFactory::class);
        $queue = self::transformToObjectQueue($container->get(GraphqlQueryInterface::class), $container);
        $queriesQueue = self::transformToObjectQueue($container->get(GraphqlQueriesInterface::class), $container);
        return $schemaFactory->createSchemaFromQueriesQueues($queue, $queriesQueue);
    }

    public static function getGraphqlHandler(Container $container): StandardServer {
        return new StandardServer(["schema" => $container->get(Schema::class)]);
    }

    public static function getFactory(Container $container): WebonyxSchemaFactory {
        return new WebonyxSchemaFactory($container->get(Registry::class));
    }

}