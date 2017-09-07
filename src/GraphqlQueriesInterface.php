<?php
namespace PsCs\Harmony\Graphql\Tool;
use GraphQL\Type\Definition\FieldDefinition;

interface GraphqlQueriesInterface { 
  public function getQueryFieldList($typeRegistry): array;
}