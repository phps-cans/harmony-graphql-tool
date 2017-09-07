<?php
namespace PsCs\Harmony\Graphql\Tool\Factory;

use Interop\Container\ContainerInterface as Container;

use PsCs\Harmony\Graphql\Tool\GraphqlTypeInterface;
use PsCs\Harmony\Graphql\Tool\Registry\Registry;
use PsCs\Harmony\Graphql\Tool\Exception\InvalidQuery;
use PsCs\Harmony\Graphql\Tool\GraphqlQueryInterface;
use PsCs\Harmony\Graphql\Tool\GraphqlQueriesInterface;

use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;

class WebonyxSchemaFactory {
    protected $registry;
    public function __construct(Registry $registry) {
        $this->registry = $registry;
    }

    public function checkArrayIsFieldsInstance($array): bool {
        foreach($array as $a) {
            if ( !($a instanceof \GraphQL\Type\Definition\FieldDefinition) ) {
                throw InvalidQuery::notWebonyxFieldInstance($q);
            }
        }
        return true;
    }

    public function createSchemaFromQueriesQueues($queryQueue, $queriesQueue): Schema {
        $fields = [];
        foreach ($queryQueue as $query) {
            $q = $query;
            if ($q instanceof GraphqlQueryInterface) {
                $fields[] = $q->getQueryField($this->registry);
            }
            elseif ($q instanceof \GraphQL\Type\Definition\FieldDefinition) {
                $fields[] = $q;
            }
        }
        foreach ($queriesQueue as $query) {
            $q = $query;
            if ($q instanceof GraphqlQueriesInterface) {
                $value = $q->getQueryFieldList($this->registry);
                if ($this->checkArrayIsFieldsInstance($value)) {
                    $fields = \array_merge($fields, $value);
                }
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
}
