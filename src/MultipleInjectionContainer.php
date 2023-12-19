<?php

namespace Zeantar\Injection;

use Zeantar\Injection\Attributes\Postfix;
use Zeantar\Injection\Attributes\Prefix;

final class MultipleInjectionContainer implements InjectionContainerInterface
{
    private \ReflectionClass $reflection;

    public array $patches = [];

    private array $instances = [];

    public function __construct()
    {
    }

    public function addPatch($className, $methodName, $patchClass)
    {
        $this->patches[$className][$methodName] = $patchClass;
        $this->instances[$className] = new $className;
    }

    public function __call($method, $args)
    {
        foreach ($this->patches as $className => $class) {
            if (array_key_exists($method, $class)) {
                $injectionClass = $class[$method];
                $injectionReflection = new \ReflectionClass($injectionClass);

                $prefixes = $postfixes = [];
                foreach ($injectionReflection->getMethods() as $injectionMethod) {
                    foreach ($injectionMethod->getAttributes() as $methodAttribute) {
                        $attributeName = $methodAttribute->getName();

                        if ($attributeName == Prefix::class) {
                            $prefixes[] = $injectionMethod->getName();
                        } elseif ($attributeName == Postfix::class) {
                            $postfixes[] = $injectionMethod->getName();
                        }
                    }
                }

                $instance = $this->instances[$className];
                $injectionReflection->setStaticPropertyValue('instance', $instance);

                $prefixReturn = $injectionClass::prefix();
                if ($prefixReturn === true) {
                    return;
                }
                $originalReturn = $instance->{$method}(...$args);
                $modifiedReturn = $injectionClass::postfix($originalReturn);

                if ($modifiedReturn !== null) {
                    return $modifiedReturn;
                }
                return $originalReturn;
            }
        }
    }
}
