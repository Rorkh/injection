<?php

namespace Zeantar\Injection;

use Zeantar\Injection\Attributes\Patch;

class Injection
{
    private static array $containers = [];

    public static function wrap(string $className): InjectionContainerInterface
    {
        if (array_key_exists($className, self::$containers)) {
            return self::$containers[$className];
        }
        return new SingularInjectionContainer(new $className);
    }

    public static function apply(string $patchClass)
    {
        $reflection = new \ReflectionClass(new $patchClass);
        $attributes = array_filter($reflection->getAttributes(), function($attribute) {
            return $attribute->getName() == Patch::class;
        });
        foreach ($attributes as $attribute) {
            $arguments = $attribute->getArguments();
            $className = $arguments['class'];

            if (!array_key_exists($className, self::$containers)) {
                self::$containers[$className] = new MultipleInjectionContainer;
            }

            self::$containers[$className]->addPatch($className, $arguments['method'], $patchClass);
        }
    }
}