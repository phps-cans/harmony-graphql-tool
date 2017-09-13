<?php
namespace PsCs\Harmony\Graphql\Tool;
use GraphQL\Type\Definition\Type;


interface GraphqlTypeInterface { 
    static public function getType($typeRegistry): Type;
}