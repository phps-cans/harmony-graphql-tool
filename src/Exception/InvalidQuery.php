<?php
namespace PsCs\Harmony\Graphql\Tool\Exception;

use Interop\Container\ContainerInterface as Container;
use PsCs\Harmony\Graphql\Tool\Registry\Registry;
use GraphQL\Type\Definition\FieldDefinition;
class InvalidQuery extends \Exception {

    public function getReadableType($object) : string {
        return \is_object($object) ? \get_class($object) : \gettype($object);
    }

    public static function notWebonyxFieldInstance($invalidInstance , string $msg = ""): void {
        throw new self(empty($msg) ? "Expected ".FieldDefinition::class." got ".$this->getReadableType($invalidInstance) : $msg);
    }

}