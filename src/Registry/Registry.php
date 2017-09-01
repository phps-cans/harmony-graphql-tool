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

   public function get($id) {
       if ($this->container->has($id)) {
         return $this->container->get($id);
       }
       if (isset($this->typeList[$id])) {
            return $this->typeList[$id];
       }
       $suffix = substr($id, -4);
       $className = "";
       if ($this->checkClassFromName($id)) {
            $className = $id;
            $this->typeList[$id] = $className::getType($this);
            return $this->typeList[$id];
       }
       else if (!$suffix || \strtoupper($suffix) !== "TYPE" || !($className = substr($id, 0, -4)) || !$this->checkClassFromName($className)) {  
           return $this->container->get($id); 
       }
       $this->typeList[$id] = $className::getType($this);
       return $this->typeList[$id];
   }

   public function has($id) {
       return $this->container->has($container) || isset($this->typeList[$id]);
   }
}