<?php
namespace Core;

class Container {
    private array $bindings = [];

    public function set($id, $factory) {
        $this->bindings[$id] = $factory;
    }

    /**
     * @throws Exception
     */
    public function get($id) {
        if (! isset($this->bindings[$id])) {
            throw new \Exception("Target binding [$id] does not exist.");
        }

        $factory = $this->bindings[$id];

        return $factory($this);
    }

    /**
     * @throws Exception
     */
    public function build(string $class) {
        try {
            $reflector = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new \Exception("Target class [$class] does not exist.", 0, $e);
        }

        // If the type is not instantiable, such as an Interface or Abstract Class
        if (! $reflector->isInstantiable()) {
            throw new \Exception("Target [$class] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // If there are no constructor, that means there are no dependencies
        if ($constructor === null) {
            return new $class;
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (! $type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                // Resolve a non-class hinted primitive dependency.
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else if ($parameter->isVariadic()) {
                    $dependencies[] = [];
                } else {
                    throw new \Exception("Unresolvable dependency [$parameter] in class {$parameter->getDeclaringClass()->getName()}");
                }
                continue;
            }

            $name = $type->getName();

            // Resolve a class based dependency from the container.
            try {
                $dependency = $this->get($name);
                $dependencies[] = $dependency;
            } catch (\Exception $e) {
                if ($parameter->isOptional()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    $dependency = $this->build($parameter->getType()->getName());
                    $this->set($name, static fn () => $dependency);
                    $dependencies[] = $dependency;
                }
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
