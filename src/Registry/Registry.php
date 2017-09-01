<?php
namespace PsCs\Harmony\Graphql\Tool\Registry;

use Interop\Container\ContainerInterface as Container;
use PsCs\Harmony\Graphql\Tool\GraphqlTypeInterface;

class Registry implements Container {

    protected $container;

    protected $typeList = [];

    public function __construct(Container $container) {
        $this->container = $container;
    }

    private function checkClassFromName($name) {
        return !empty($name) && class_exists($name, false) && ($interfaces = class_implements($name, false))
        && isset($interfaces[GraphqlTypeInterface::class]);   
    }

    private function getClassNameFromId($id): ?string {
        if ($this->checkClassFromName($id)) {
            return $id;
        }

       $suffix = substr($id, -4);
       if ($suffix && \strtoupper($suffix) === "TYPE" && ($className = substr($id, 0, -4)) && $this->checkClassFromName($className)) {  
            return $className;
        }
        return null;
    }

   public function get($id) {
       if ($this->container->has($id)) {
         return $this->container->get($id);
       }
       $className = $this->getClassNameFromId($id);
       if (!$className) {
            return $this->container->get($id); // Must throw exception 
       }
       if (isset($this->typeList[$className])) {
            return $this->typeList[$className];
       }
       $this->typeList[$className] = $className::getType($this);
       return $this->typeList[$className];
   }

   public function has($id) {
       return $this->container->has($container) || isset($this->typeList[$id]);
   }
}