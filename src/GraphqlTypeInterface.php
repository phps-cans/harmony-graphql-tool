<?php
namespace PsCs\Harmony\Graphql\Tool;
use GraphQL\Type\Definition\ObjectType;


interface GraphqlTypeInterface { 
    static public function getType($typeRegistry): ObjectType;
}