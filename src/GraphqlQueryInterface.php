<?php
namespace PsCs\Harmony\Graphql\Tool;
use GraphQL\Type\Definition\FieldDefinition;

interface GraphqlQueryInterface { 
  public function getQueryField($typeRegistry): FieldDefinition;
}