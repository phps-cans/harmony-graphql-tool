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

   public function get($id) {
       if ($this->container->has($id)) {
         return $this->container->get($id);
       }
       if (isset($this->typeList[$id])) {
            return $this->typeList[$id];
       }
       $suffix = substr($id, -4);
       if ($suffix && \strtoupper($suffix) === "TYPE" && !(class_exists($id, false) && (($interfaces = class_implements($id, false))
       && isset($interfaces[GraphqlTypeInterface::class])) )) {
            $className = substr($id, 0, -4);
       }
       else {
           $className = $id;
       }
       
       if (class_exists($className, false) 
         && ($interfaces = class_implements($className, false))
         && isset($interfaces[GraphqlTypeInterface::class])) { // TODO if the class does  not implement GraphqlTypeInterface, develop a discover strategy
            $this->typeList[$id] = $className::getType($this);
            return $this->typeList[$id];
       }
       return $this->container->get($id); // Must throw an exception
   }

   public function has($id) {
       return $this->container->has($container) || isset($this->typeList[$id]);
   }
}